<?php use app\components\VerificationWidget; ?>

<h1 id="introduction">Introduction</h1>
<p><strong>Welcome</strong> to the VerCors tutorial! In this tutorial, we will look at what VerCors is, what it can do, and how <em>you</em> can use it.</p>
<p>VerCors is a toolset for software verification. It can be used to reason about programs written in Java, C, OpenCL and PVL, which is a <em>Prototypal Verification Language</em> that is often used to demonstrate and test the capabilities of VerCors.</p>
<p>In this tutorial, we will first take a brief look at what software verification is, and where VerCors fits in. Then, we will discuss the syntax of <a href="https://github.com/utwente-fmt/vercors/wiki/Prototypal-Verification-Language">PVL</a> and <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Syntax">VerCors</a>. Once we have a basic idea of how things work, we look at several more advanced concepts, either of VerCors (e.g. <a href="https://github.com/utwente-fmt/vercors/wiki/Resources-and-Predicates">resources</a>) or of the input languages (e.g. <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Inheritance">inheritance</a>). You can find an overview of the chapters on the right.</p>
<h2 id="software-verification">Software Verification</h2>
<p>Nowadays, we as a society rely heavily on software, and software errors can have a major impact, even causing deaths. Thus, software developers strive to reduce the number of software errors as much as possible. <em>Software verification</em> is the task of reasoning about the behaviour of the software, in order to ensure that it behaves correctly. The most basic case could be considered to be the compiler, which checks that the program e.g. does not misspell a name. Only if the compiler does not find any errors, then the program can be executed. However, many errors that are more intricate can slip past the compiler. To catch these, there are two possibilities: <em>Static analysis</em> and <em>dynamic analysis</em>. Dynamic analysis runs the program and watches its behaviour. One example is testing, where you provide the software with a concrete input, let it compute an output and check that it is the expected one. While this can show errors, it cannot guarantee the absence of errors: Maybe it only works for this specific input, and even that only when the sun is shining. <em>Static analysis</em> looks at the source code itself, and can thus reason in more general terms. The compiler is part of this category, and so is VerCors.</p>
<p>Different tools provide different levels of analysis. As an example, let's take a division by zero, which is not allowed in mathematics and would cause the software to misbehave. In the most simple case, we could search for expressions like <code>1/0</code>, which we recognise as bad immediately. But what about <code>1/x</code>? Maybe <code>x</code> is zero, maybe not. Some tools will check the program to find all possible values that <code>x</code> can take. But this is often difficult to decide, and the tools often approximate (e.g. instead of saying "1, 2 or 4", they say "the interval [1,4]", thereby also including the value 3). This can lead to false results, e.g. complaining about programs that are actually correct. Other tools require the user (meaning the software developer) to specify which values <code>x</code> can take. This requires much more effort by the user, who has to <em>annotate</em> the code, i.e. provide additional information inside the program that is not needed to run the program, but only for analysing it. As a reward for the additional effort, the user can get more accurate results. VerCors follows this approach, requiring the user to provide specifications as annotations. <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Syntax">Chapter 3</a> of this tutorial describes the syntax for these annotations.</p>
<p>Two important categories of annotations are <em>assumptions</em> and <em>assertions</em>. The tools then try to prove: If before executing a piece of code, the assumptions hold, then afterwards the assertions hold. As an example, consider the following code:</p>
<!-- testMethod -->

<?= VerificationWidget::widget(['initialLanguage' => 'java', 'initialCode' => base64_decode('Y2xhc3MgVGVzdCB7CiAgICBpbnQgZm9vKGludCBhcmcpIHsKICAgICAgcmV0dXJuIDEwL2FyZzsKICAgIH0KfQ==')]) ?>
<p>If we <em>assume</em> that the method is only invoked with arguments <code>arg&gt;0</code>, then we can <em>assert</em> that no division by zero occurs, and even that the result is between 0 and 10. These specifications are the <em>pre-condition</em> and <em>post-condition</em> of the method, respectively. When analysing the method's body, the pre-condition is assumed, and the tool tries to assert the post-condition. When the method is used, i.e. another part of the code invokes for instance <code>foo(42)</code>, then the tool tries to assert the pre-condition before the method invocation (i.e. check that it is allowed to call the method like this), and then assumes the post-condition. This makes the analysis modular. Note that if the pre-condition is unsatisfiable (e.g. two contradictory conditions, or a simple <code>false</code>), then verifying the method will succeed with any post-condition, because the implication "if pre-condition then post-condition" is trivially true. Thus, users must be careful in their choice of assumptions.</p>
<h2 id="vercors">VerCors</h2>
<p>As mentioned above, VerCors is a static verification tool that relies on annotations in the code specifying its behaviour. It particularly targets parallel and concurrent programs, as they are more difficult to understand intuitively and thus are more error-prone than sequential programs. One typical example is two parts of the program accessing the same memory location at the same time. This can lead to the unintuitive fact that, right after one thread wrote a value to a variable, that variable might already have an entirely different value due to the other thread jumping in between and changing it. To avoid one thread invalidating the properties and invariants maintained and observed by all the other threads, VerCors uses <em>Concurrent Separation Logic (CSL)</em> as its logical foundation. CSL is a program logic that has a very strong notion of <em>ownership</em> in the form of <em>(fractional) permissions</em>: A thread can only read from, or write to, shared memory if it owns enough permission to do so. So just because a variable is on the heap, shared and technically accessible by everyone, that does not mean that just anyone can go ahead and use it; they need to coordinate with the others by getting permission first. The specification language of VerCors has constructs to deal with ownership and permissions. An advantage of concurrent separation logic is that, due to the explicit handling of ownership, we get properties like data-race freedom and memory safety for free; these properties are consequences of the soundness argument of the logic. You will notice that the handling of permissions makes up a significant part of the verification effort, and <em>"Insufficient Permissions"</em> is a frequent complaint by VerCors in the beginning. So while VerCors can be used to analyse sequential programs, it always requires this overhead of managing permissions. Therefore, if you only wish to verify sequential algorithms, it may be worthwhile to look at alternative tools such as OpenJML and KeY, which do not use CSL as their logical foundation. For more info on CSL, ownership and permissions, see <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">Chapter 4</a>.</p>
<p>While VerCors currently supports Java, C (incl. OpenMP), OpenCL and PVL, it is designed to be modular, in the sense that extensions to other input languages (like, for example, C#) with parallel and concurrent language constructs can be added without much difficulty. For that, the aim for VerCors is to allow reasoning over many different general concurrency structures, like statically-scoped concurrency (e.g. GPU kernels), dynamically-scoped concurrency (e.g. fork/join parallelism), and automated parallelism (e.g. programs that are automatically parallelised using OpenMP).</p>
<p>Note that VerCors checks for <em>partial correctness</em>, meaning that if the program terminates, then it satisfies its post-conditions. No proof is attempted to check whether the program actually terminates.</p>
<p>It is worth noting that VerCors is not the only tool that can perform static verification on annotated programs; there are actually many tools that can do a very similar job. Examples of such tools are: Dafny, OpenJML, KeY, VeriFast, and VCC. However, VerCors distinguishes itself by focussing on <em>different parallel and concurrent language constructs</em> (e.g. Dafny, OpenJML, and KeY only allow verifying sequential programs) of <em>high-level programming languages</em> (e.g. VCC only allows to verify C programs). Moreover, VerCors is not designed to be language-dependent, but instead focusses on verification techniques for <em>general</em> concurrency patterns.</p>
<h3 id="underneath-the-hood">Underneath the Hood</h3>
<p>In case that you are interested in the architecture within VerCors, here is a brief introduction: VerCors parses your input file(s), which contain the program in the target language (e.g. Java) as well as the specifications, into an <em>abstract syntax tree (AST)</em>. It then traverses the AST multiple times to perform various checks (e.g. type check) and transformations. It then passes the modified AST on to the <a href="https://www.pm.inf.ethz.ch/research/viper.html">Viper backend</a> (some of the transformations are for example needed to turn your program into something Viper understands). Viper then performs the actual analysis, using the SMT solver <em>Z3</em>. The results from Z3 / Viper are then related back to your original input file(s) and presented to you in a more human-understandable way.</p>
<h1 id="installing-and-running-vercors">Installing and Running VerCors</h1>
<p>You can install VerCors by either using a release (recommended for beginners), or by building VerCors from its source code.</p>
<h2 id="using-a-release">Using a Release</h2>
<h3 id="installation">Installation</h3>
<p>VerCors requires a java runtime environment (version 8 or later), as well as clang if you want support for C.</p>
<h4 id="linux">Linux</h4>
<p>Currently we support debian-based systems; let us know if you need something else! Install the dependencies:</p>
<pre class="shell"><code>sudo apt install clang openjdk-8-jre 
</code></pre>
<p>Obtain the latest deb release of VerCors <a href="https://github.com/utwente-fmt/vercors/releases/latest">here</a>, and install it by running:</p>
<pre class="shell"><code>sudo dpkg -i Vercors_x.y.z_all.deb
</code></pre>
<h4 id="mac">Mac</h4>
<p>You can for example obtain the dependencies through homebrew:</p>
<pre class="shell"><code>brew cask install java
</code></pre>
<p>This should install the latest release of OpenJDK. Clang should already be installed through XCode.</p>
<p>Obtain the latest zip release of VerCors <a href="https://github.com/utwente-fmt/vercors/releases/latest">here</a> and unzip it. You can find the run script for VerCors in the <code>bin</code> subdirectory.</p>
<h4 id="windows">Windows</h4>
<p>You can obtain a java runtime environment e.g. <a href="https://jdk.java.net">here</a>. Make sure that the environment variable <code>JAVA_HOME</code> points to wherever you unpack the JDK. clang can be obtained as part of the llvm toolchain <a href="https://clang.llvm.org/">here</a>. Make sure that <code>clang</code> is added to the path.</p>
<p>Next, download the latest zip release of VerCors <a href="https://github.com/utwente-fmt/vercors/releases/latest">here</a> and unzip it. You can find the batch script for VerCors in the <code>bin</code> subdirectory.</p>
<h3 id="running-vercors">Running VerCors</h3>
<p>The VerCors toolset can be used by running <code>vercors --silicon &lt;filepath&gt;</code>, with <code>&lt;filepath&gt;</code> the path of the (Java, C, or PVL) file to verify.</p>
<h2 id="building-from-source-code">Building from source code</h2>
<h3 id="installation-1">Installation</h3>
<p>When building VerCors, you additionally need these dependencies:</p>
<ul>
<li>A Java <em>Development</em> Kit, version 8 or greater, either OpenJDK or Oracle.</li>
<li>Git (on Windows you need Git Bash, see <a href="https://git-scm.com/downloads">https://git-scm.com/downloads</a>)</li>
<li>Mercurial (See <a href="https://www.mercurial-scm.org/downloads">https://www.mercurial-scm.org/downloads</a>)</li>
<li>Scala SBT, version 1.3.0 or greater (see <a href="http://www.scala-sbt.org">http://www.scala-sbt.org</a> for instructions)</li>
</ul>
<ol>
<li>Clone the VerCors repository using <code>git clone https://github.com/utwente-fmt/vercors.git</code> and move into the cloned directory, <code>cd vercors</code>.</li>
<li>Run <code>sbt compile</code> to compile VerCors.</li>
<li>Test whether the build was successful by running <code>./bin/vct --test=examples/manual --tool=silicon --lang=pvl,java --progress</code>.</li>
</ol>
<p>The last command tests the VerCors installation by verifying a large collection of examples (from the <code>./examples</code> directory). This command should eventually report that <code>all ? tests passed</code>. There are also instructions for importing VerCors into either eclipse or IntelliJ IDEA <a href="https://github.com/utwente-fmt/vercors/wiki/IDE-Import">here</a>.</p>
<h3 id="running-vercors-1">Running VerCors</h3>
<p>The VerCors toolset can be used by running <code>./bin/vct --silicon &lt;filepath&gt;</code>, with <code>&lt;filepath&gt;</code> the path of the (Java, C, or PVL) file to verify.</p>
<h1 id="prototypical-verification-language">Prototypical Verification Language</h1>
<p><em>Maybe this page should be merged with <a href="https://github.com/utwente-fmt/vercors/wiki/PVL-Syntax">https://github.com/utwente-fmt/vercors/wiki/PVL-Syntax</a></em> ?</p>
<p>This page discusses the language features of PVL, the Prototypal Verification Language of VerCors. In particular, it elaborates on design choices and gives implementation details.</p>
<h5 id="syntax-highlighting">Syntax highlighting</h5>
<p>VerCors provides syntax highlighting support for PVL in <a href="https://macromates.com/download">TextMate 2</a> (MacOS X) and <a href="https://www.sublimetext.com">Sublime Text</a> (Linux and Windows) as a TextMate Bundle. The bundle is located at <code>./util/VercorsPVL.tmbundle</code>. On MacOS X for TextMate 2 you can simply double click the <code>.tmbundle</code> file to install it. Sublime Text requires you to copy the bundle content to some directory:</p>
<ol>
<li>In Sublime Text, click on the <code>Preferences &gt; Browse Packages…</code> menu.</li>
<li>Create a new directory and name it <code>VercorsPVL</code>.</li>
<li>Move the contents of <code>VercorsPVL.tmbundle</code> (that is, the <code>./Syntaxes</code> directory) into the directory you just created.</li>
<li>Restart Sublime Text.</li>
</ol>
<p><a href="https://code.visualstudio.com">Visual Studio Code</a> (VS Code) also has support for TextMate bundles to do syntax highlighting (VS Code runs on Windows, Linux and OSX). Click <a href="https://code.visualstudio.com/docs/extensions/themes-snippets-colorizers">here</a> for instructions on how to install this (I have not tested this however).</p>
<h3 id="language-features">Language features</h3>
<h5 id="prefix-and-postfix-operators">Prefix and postfix operators</h5>
<p>PVL has support for the postfix incremental and decremental operators, which are <code>x++</code> and <code>x--</code>. However, these two constructs are implemented as statements rather than expressions; they are simply syntactic sugar for <code>x = x + 1</code> and <code>x := x - 1</code>, respectively. Currently, VerCors does not support expressions with side-effects, and an expression <code>x++</code> (actually, <code>x = x + 1</code> is also considered an expression in Java) would have the side-effect of updating <code>x</code>. For this reason, the prefix versions, <code>++x</code> and <code>--x</code>, are also not implemented (and implementing them as statements gives the same reduction as with <code>x++</code> and <code>x--</code>).</p>
<p>Implementing support for expressions with side-effects is mostly an engineering effort, not so much a theoretical problem. One could simply transform Java programs that use expressions with side-effects to equivalent programs without such expressions (as a pre-processing step in VerCors). The ABC library (directed by Stephen Siegel, see <a href="https://vsl.cis.udel.edu/trac/civl/wiki/ABC">https://vsl.cis.udel.edu/trac/civl/wiki/ABC</a>) provides such a transformation mechanism that we can probably directly use. The code of ABC can be obtained by running <code>svn checkout svn://vsl.cis.udel.edu/abc</code>.</p>
<h1 id="syntax">Syntax</h1>
<p><em>This section describes the syntax of the specification language, which is independent of the target language. Thus, unless otherwise stated, all features are supported for all languages.</em></p>
<p>In this part of the tutorial, we will take a look at how specifications are expressed in VerCors. While this tutorial provides more detailed explanations of the various constructs, a concise list can be found in the <a href="https://github.com/utwente-fmt/vercors/wiki/PVL-Syntax">PVL Syntax Reference</a> in the annex. The specification language is similar to <a href="http://www.eecs.ucf.edu/~leavens/JML/index.shtml">JML</a>.</p>
<p>In VerCors, specifications consist largely of statements, each of which has to end with a semicolon.</p>
<p>Depending on the input language, specification are integrated in two different ways: In most languages, such as Java and C, the specifications are provided in special comments. These special comments are either line comments starting with <code>//@</code>, or block comments wrapped in <code>/*@</code> and <code>@*/</code>.</p>
<div class="sourceCode" id="cb4"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb4-1" title="1"><span class="dt">int</span> x = <span class="dv">2</span>;</a>
<a class="sourceLine" id="cb4-2" title="2"><span class="co">//@ assert x == 2;</span></a>
<a class="sourceLine" id="cb4-3" title="3"><span class="dt">int</span> y = x + <span class="dv">3</span>;</a>
<a class="sourceLine" id="cb4-4" title="4"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb4-5" title="5"><span class="co">assert y == 5;</span></a>
<a class="sourceLine" id="cb4-6" title="6"><span class="co">@*/</span></a></code></pre></div>
<p>Since these are simply a type of comment, regular compilers ignore them and can still compile the program. However, VerCors can interpret them. Note that this style of comment matches the JML definition, so VerCors is not the only tool using them. However, the exact interpretation of the comments may vary between tools, so a valid specification in VerCors may not be recognised by another tool and vice versa.</p>
<p><strong>Tip</strong> Always remember to place the <code>@</code>! It can be aggravating to spend significant time debugging, just to realise that parts of the specification were put in regular comments and ignored by the tool.</p>
<p>For PVL, the specifications are inserted directly into the code, <em>without</em> the special comments around them:</p>
<div class="sourceCode" id="cb5"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb5-1" title="1"><span class="dt">int</span> x = <span class="dv">2</span>;</a>
<a class="sourceLine" id="cb5-2" title="2">assert x == <span class="dv">2</span>;</a>
<a class="sourceLine" id="cb5-3" title="3"><span class="dt">int</span> y = x + <span class="dv">3</span>;</a>
<a class="sourceLine" id="cb5-4" title="4">assert y == <span class="dv">5</span>;</a></code></pre></div>
<p>Given that the syntax is otherwise identical, we will use the commented version in the rest of the tutorial, to make the separation between specifications and target code more obvious. The examples in this chapter are in Java, but you can find the respective examples in other languages on the <a href="https://vercors.ewi.utwente.nl/try_online/examples?ExampleSearch%5Bid%5D=&amp;ExampleSearch%5Btitle%5D=&amp;ExampleSearch%5Bfeature%5D=&amp;ExampleSearch%5Bsource%5D=1&amp;ExampleSearch%5Blanguagename%5D=">website</a> (as well as a Java file of all the examples below).</p>
<h2 id="expressions">Expressions</h2>
<p>In principle, the specifications extend the language of the program you analyse (such as Java, C or PVL). So if you analyse a Java program, you can use expressions like <code>a&amp;&amp;b</code> or <code>x==y+1</code> in the specifications just like in Java. However, specifications must be free of side-effects (on the program state), otherwise the compiler ignoring the specifications would compute something different than the analysis tool that takes the specifications into account. As a result, for example method calls are not allowed. Note that later on, we will discuss so-called ghost code, such as defining additional variables purely for the sake of specification. Depending on the circumstances, side-effects on the state of ghost variables are allowed. But for the beginning, let's only consider specifications without side-effects.</p>
<p>VerCors extends the expressions of the target language by a few more features that you can use in specifications. One of them is the implication operator <code>==&gt;</code>, which works like you would expect from a logical implication. A common usage is <code>requires x!=null ==&gt; &lt;some statement about fields of x&gt;</code>. Note that the implication binds less than equality or conjunction, so <code>a==&gt;b&amp;&amp;c</code> is equivalent to <code>a==&gt;(b&amp;&amp;c)</code>. You need to explicitly use parentheses if the operators shall associate differently.</p>
<p>Two other new operators are <code>**</code> and <code>-*</code> from separation logic. <code>**</code> is the separating conjunct, while <code>-*</code> is the separating implication (or "magic wand"). For more on this, see the Chapters on <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">Permissions</a> and <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Magic-Wands">Magic Wands</a>.</p>
<p>The target language is also extended to include new data types. A simple case is the boolean type <code>bool</code>, which can be useful in specifications if the target language has no boolean type (e.g. old C). If the target language does support boolean (e.g. Java), this is not needed (but can be used nonetheless). More interestingly, the new types include the generic axiomatic data types <code>seq&lt;T&gt;</code>, <code>set&lt;T&gt;</code> and <code>bag&lt;T&gt;</code> (with <code>T</code> being a type), which describe sequences, sets and multi-sets, respectively. For more information on them and their supported operations (such as getting the size, and indexing elements), see the <a href="https://github.com/utwente-fmt/vercors/wiki/Axiomatic-Data-Types">respective chapter</a>. Another new container type is <code>option&lt;T&gt;</code>. It can either take the predefined value <code>None</code>, or it is <code>Some(val)</code> with <code>val</code> being an expression of type <code>T</code>. For example, you might use</p>
<div class="sourceCode" id="cb6"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb6-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb6-2" title="2"><span class="co">ghost option&lt;int&gt; x = (arr==null ? None : Some(arr.length);</span></a>
<a class="sourceLine" id="cb6-3" title="3"><span class="co">@*/</span></a></code></pre></div>
<p>(You can for now ignore the <code>ghost</code> keyword, it will be discussed later in this chapter).</p>
<p>An important new type are fractions, <code>frac</code>. VerCors uses concurrent separation logic (CSL) to manage the ownership and permissions to access heap locations. A permission is a value from the interval [0,1], with 0 meaning no permission, and 1 meaning full write access. A permission in between those values means only read access. To express these permission values, the type <code>frac</code> is used. To give a value to a variable of type <code>frac</code>, the new operator of <em>fractional division</em> can be used: While <code>2/3</code> indicates the classical integer division, which in this example gives <code>0</code>, using the backslash instead gives a fraction: <code>2\3</code>. For more on this topic, including some additional keywords for short-hand notations, see the chapter on <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">permissions</a>.</p>
<p>Sometimes, you might create complicated expressions and want to use helper variables to simplify them. However, certain constructs only allow for expressions, and not for statements such as variable declarations. To alleviate that, there is the <code>\let</code> construct, which defines a variable just for a single expression: <code>( \let type name = expr1 ; expr2 )</code>, where <code>type</code> is the type of the helper variable, <code>name</code> its name, <code>expr1</code> defines its value, and <code>expr2</code> the complicated expression that you can now simplify by using the helper variable. Example:</p>
<div class="sourceCode" id="cb7"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb7-1" title="1"><span class="co">//@ assert (\let int abs_x = (x&lt;0 ? -x : x); y==(z==null ? abs_x : 5*abs_x));</span></a></code></pre></div>
<h3 id="quantifiers">Quantifiers</h3>
<p>Note that most target languages, such as Java and C, support array types, such as <code>int[]</code>. Sometimes, you might want to reason about all elements of the array, or need permission to access all those elements. How do you do that, especially if the size of the array is not known beforehand (e.g. user input)? To do that, VerCors supports using <em>quantifiers</em> in the specifications: <code>(\forall varDecl; cond; expr)</code>. The syntax is similar to the header of a for loop: <code>varDecl</code> declares a variable, e.g. <code>int i</code>. <code>cond</code> is a boolean expression describing a boundary condition, restricting the declared variable to the applicable cases, e.g. defining the range of the integer <code>0&lt;=i &amp;&amp; i&lt;arr.length</code>. <code>expr</code> is the boolean expression you are interested in, such as a statement you want to assert. Note that the parentheses are mandatory. Such a quantified expression typically relates to an array, or an axiomatic data type like a <code>seq</code>. However, it is not restricted to such cases. Here is an example:</p>
<div class="sourceCode" id="cb8"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb8-1" title="1"><span class="co">//@ requires (\forall int i ; 0&lt;=i &amp;&amp; i&lt;arr.length ; arr[i]&gt;0);</span></a>
<a class="sourceLine" id="cb8-2" title="2"><span class="dt">void</span> <span class="fu">foo</span>(<span class="dt">int</span>[] arr) {...}</a></code></pre></div>
<p>Note that in practice, you would also have to specify permissions to access the values in the array. More on that in the <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">next chapter</a>.</p>
<p><strong>Tip</strong> If you want to quantify over more than one variable (e.g. saying <code>arr1[i] != arr2[j]</code> for all <code>i</code> and <code>j</code>), use nesting: <code>(\forall int i; 0&lt;=i &amp;&amp; i&lt;arr1.length; (\forall int j; 0&lt;=j &amp;&amp; j&lt;arr2.length; arr1[i]!=arr2[j]))</code>.</p>
<p>If your boundary condition is an interval (like in the examples above), you can use the shorter notation <code>(\forall type name = e1 .. e2 ; expr)</code>, where <code>type</code> is a type that supports comparison with <code>&lt;</code> (e.g. integer, fraction), <code>name</code> is the name of the quantified variable, <code>e1</code> and <code>e2</code> are expressions defining the interval bounds (lower bound inclusive, upper bound exclusive) and <code>expr</code> is the expression you are interested in: <code>(\forall int i = 0 .. arr.length ; arr[i]&gt;0)</code>. Note that, depending on the circumstances, spaces are necessary around the <code>..</code>.</p>
<p>A brief glimpse behind the curtain to the inner workings of VerCors: In the method body, VerCors might encounter a statement like <code>x = arr[1]</code>, and it now needs to apply the generic knowledge of the quantifier to the concrete case <code>arr[1]</code>. This is done by <em>instantiating</em> the quantifier, which basically replaces the quantified variable(s), in this case <code>i</code>, with concrete values. But this is only done when necessary, so when the concrete case <code>arr[1]</code> is actually encountered. If the instantiation would already happen eagerly when the <code>\forall</code> is encountered, that might generate a lot of unnecessary overhead, instantiating the expression for values that are never actually needed. In fact, depending on the type of the quantified variable and the range expression, there might be infinitely many possible instantiations. Recognising that the quantifier must be instantiated was fairly easy in this case, but for more complex expression it can become rather difficult. In those cases, VerCors might use heuristics, and even randomisation. This can lead to VerCors verifying a program successfully, and when you call it again with the exact same program, the analysis takes forever. So if you experience such behaviour, quantified expressions are a likely cause.</p>
<p>Even worse than <code>\forall</code> is the <code>\exists</code> quantifier: <code>(\exists varDecl; cond; expr)</code>, where <code>varDecl</code> declares a variable, e.g. <code>int i</code>, <code>cond</code> is a boolean boundary condition like before, and <code>expr</code> is the expression of interest. For instance, we could use a similar example as above, but requiring that <em>at least one</em> array element is positive:</p>
<div class="sourceCode" id="cb9"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb9-1" title="1"><span class="co">//@ requires (\exists int i ; 0&lt;=i &amp;&amp; i&lt;arr.length ; arr[i]&gt;0);</span></a>
<a class="sourceLine" id="cb9-2" title="2"><span class="dt">void</span> <span class="fu">foo</span>(<span class="dt">int</span>[] arr) {...}</a></code></pre></div>
<p><strong>Note</strong> Because <code>\exists</code> quantifiers are even more likely to cause issues than <code>\forall</code>, they should be <strong>used with care</strong>!</p>
<h2 id="assumptions-and-assertions">Assumptions and Assertions</h2>
<p>Two of the most important specification elements are <em>assumptions</em> and <em>assertions</em>. These are expressions that can evaluate to either <code>true</code> or <code>false</code>, depending on the program state. As explained in the <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Introduction">introductory chapter</a>, VerCors tries to verify that, if the assumptions hold before executing a piece of code, then the assertions hold afterwards. While other, more complex specification constructs might have a similar meaning to assumptions or assertions in certain contexts, you can explicitly provide them using the <code>assume</code> keyword and the <code>assert</code> keyword, respectively:</p>
<div class="sourceCode" id="cb10"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb10-1" title="1"><span class="dt">int</span> x = <span class="dv">2</span>;</a>
<a class="sourceLine" id="cb10-2" title="2"><span class="co">//@ assume x == 1;</span></a>
<a class="sourceLine" id="cb10-3" title="3"><span class="dt">int</span> y = x + <span class="dv">3</span>;</a>
<a class="sourceLine" id="cb10-4" title="4"><span class="co">//@ assert y == 4;</span></a></code></pre></div>
<p>This example verifies successfully, because under the provided assumption of Line 2, the assertion in Line 4 is indeed true. Note that the assumption of Line 2 is actually contradicting the "real" state at this point. This can easily lead to "wrong" verification results, such as VerCors asserting the statement in the example despite it not actually being true. This is not an error in VerCors, it is how the concept of pre- and post-conditions works. One particularly noteworthy case is when the assumption cannot actually be true, such as two contradictory conditions (e.g. <code>x&gt;0 &amp;&amp; x&lt;0</code>) or simply <code>false</code>. From such an unsatisfiable state, anything can be derived, and any assertion verifies successfully. Therefore, users have to be careful in what they specify in assumptions, and explicit <code>assume</code> statements should be avoided. However, they can be helpful e.g. for debugging. <code>assert</code> statements can also help debugging. Additionally, they can actually help in the verification process: Asserting intermediate results of a complex computation can help proving valid results that would otherwise be difficult to prove.</p>
<p>VerCors also supports a <code>refute</code> statement, which is the opposite of <code>assert</code>. Internally, <code>refute expr;</code> is transformed into <code>assert !(expr);</code>, i.e. asserting the negation. Note that a failing <code>assert expr;</code> is not equivalent to a successful <code>refute expr;</code>. For example, if we know nothing about the value of a variable <code>a</code>, then both <code>assert a&gt;0;</code> and <code>refute a&gt;0;</code> will fail, as <code>a</code> <em>could</em> be greater than zero, but it also could be less.</p>
<h3 id="pre--and-post-conditions">Pre- and Post-Conditions</h3>
<p>The concept of <em>pre-conditions</em> and <em>post-conditions</em> of methods is closely related to assumptions and assertions. Pre-conditions specify in what state the program is expected to be whenever the method is called, and post-conditions describe the state in which the method leaves the program when it is done. This leads to a modular approach to verification: To verify the body of a method, we can do localised reasoning and check: Assuming that the method's pre-conditions are true, does the method then behave correctly and generate the expected output, i.e. can the post-condition be asserted? We do not need to concern ourselves with all the different program locations where the method is called, and check all the different states the program can be in. For example we do not need to check all call sites to see what values a method parameter might have, we just assume that its value satisfies the pre-condition and focus our analysis on the method itself. In VerCors, pre-conditions are statements using the <code>requires</code> keyword, and post-conditions use <code>ensures</code>. They are placed above the method header, and these keywords can only be used in combination with a method header:</p>
<div class="sourceCode" id="cb11"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb11-1" title="1"><span class="co">/*@ </span></a>
<a class="sourceLine" id="cb11-2" title="2"><span class="co"> requires x == 2;</span></a>
<a class="sourceLine" id="cb11-3" title="3"><span class="co"> ensures y == 5;</span></a>
<a class="sourceLine" id="cb11-4" title="4"><span class="co">@*/</span></a>
<a class="sourceLine" id="cb11-5" title="5"><span class="kw">public</span> <span class="dt">void</span> <span class="fu">foo</span>(<span class="dt">int</span> x) {</a>
<a class="sourceLine" id="cb11-6" title="6">  y = x + <span class="dv">3</span>;</a>
<a class="sourceLine" id="cb11-7" title="7">}</a></code></pre></div>
<p>In this example, <code>y</code> is a global variable. Note that in reality, permissions would be required to access global variables (see <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">following chapter</a>). We omitted these here for simplicity.</p>
<p>The example above verifies successfully. Changing the post-condition to <code>y==6</code> would cause VerCors to raise an error, due to the post-condition not being a consequence of combining the pre-condition and the method body.</p>
<p>Whenever the method is called, VerCors checks that the program state right before the method invocation satisfies the pre-condition, and raises an error if that is not the case. Then, it assumes that the specifications of the post-condition hold after the invocation, without considering what the method body actually does internally:</p>
<div class="sourceCode" id="cb12"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb12-1" title="1"><span class="dt">void</span> <span class="fu">bar</span>() {</a>
<a class="sourceLine" id="cb12-2" title="2">  <span class="dt">int</span> a = <span class="dv">2</span>;</a>
<a class="sourceLine" id="cb12-3" title="3">  <span class="fu">foo</span>(a);</a>
<a class="sourceLine" id="cb12-4" title="4">  a = y + <span class="dv">5</span>;</a>
<a class="sourceLine" id="cb12-5" title="5">  <span class="co">//@ assert a == 10;</span></a>
<a class="sourceLine" id="cb12-6" title="6">}</a></code></pre></div>
<p>This example verifies successfully, because when the method <code>foo</code> is called, the value of the parameter <code>a</code> fulfils the pre-condition of being 2; and the post-condition implies that in Line 5, <code>a</code> indeed has the value 10. If we changed the initial assignment to <code>a=1</code>, then the pre-condition of <code>foo</code> would not be satisfied, and <code>bar</code> would fail to verify, while <code>foo</code> itself would still pass the checks successfully.</p>
<p>This leads to a modular approach to verification, where each method body can be verified independently, only relying on the pre- and post-conditions of the methods it uses, but not caring what they do internally. The pre- and post-condition of a method together form its <em>contract</em>. The method's behaviour is bound to be as specified in the contract, but no commitment is made regarding things not mentioned in the contract. For example, if a method <code>foo</code> has permission to access a global variable <code>z</code> and the contract does not mention the value of <code>z</code>, then the method is allowed to do whatever it likes to that variable. Consequently, any method <code>bar</code> calling <code>foo</code> must take into account that <code>z</code> could have any arbitrary value after the call to <code>foo</code> returns. Therefore, the author of <code>foo</code> should specify what happens to all the variables it has access to, even if they remain unchanged, in order to allow the developer of <code>bar</code> to make reasonable statements about their own method. While you might now fear that you have to specify huge contracts about all the things your method does <em>not</em> do, rest assured: In practice, the permissions, which a method has, restrict heavily what it can do, and it is simply impossible for <code>foo</code> to do anything bad to most of your precious variables, because it does not have access to them. But let this be a lesson to not be too generous with your permissions, and only give a method access to the variables it actually needs.</p>
<p>If something remains unchanged and you want to add it to both pre- and post-condition, you can use the short-hand notation <code>context expr;</code>. This is an abbreviation for <code>requires expr; ensures expr;</code>:</p>
<div class="sourceCode" id="cb13"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb13-1" title="1"><span class="co">//@ context y == 5;</span></a>
<a class="sourceLine" id="cb13-2" title="2"><span class="dt">int</span> <span class="fu">incr</span>(<span class="dt">int</span> x) {</a>
<a class="sourceLine" id="cb13-3" title="3">  <span class="dt">int</span> res = x + <span class="dv">2</span>*y;</a>
<a class="sourceLine" id="cb13-4" title="4">  <span class="co">//@ assert res == x + 10;</span></a>
<a class="sourceLine" id="cb13-5" title="5">  <span class="kw">return</span> res;</a>
<a class="sourceLine" id="cb13-6" title="6">}</a></code></pre></div>
<p>This is particularly useful when dealing with permissions, as a method will often require access to a variable, and then return this permission back to the call site.</p>
<p>Pre- and post-conditions are processed in-order, so swapping the order of two pre-conditions could result in a failed verification (e.g. you need to specify the permissions first, before you can use the respective heap variable in a pre-condition).</p>
<p>Two other interesting constructs that are often used in method contracts are <code>\result</code> and <code>\old</code>. When dealing with a non-void method like the example above, <code>\result</code> can be used in the post-condition to refer to the return value of the method. So we could move the assert statement in the example into the method contract as a post condition <code>ensures \result == x+10;</code>. The <code>\old</code> construct can be used to refer to the value of an expression before executing the method. This can be especially useful in post-conditions, when you want to relate the value after the method's execution to the one before, e.g. <code>ensures y == \old(y);</code>. However, it can also be used at any point in the body of the method to refer back to the value that the expression had at the beginning of the method. Note that you can also place more complicated expressions within <code>\old</code> than just a simple variable, for example an in-place algorithm to remove an element from a list might say <code>ensures list.length == \old(list.length - 1)</code>. But one important restriction is that the expression has to be free of side effects, even on the ghost state. Obviously, <code>\result</code> cannot occur inside <code>\old</code>, as the return value is not known at the beginning of the method. So we can adapt the previous example:</p>
<div class="sourceCode" id="cb14"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb14-1" title="1"><span class="co">//@ ensures y == \old(y);</span></a>
<a class="sourceLine" id="cb14-2" title="2"><span class="co">//@ ensures \result == x + 2*y;</span></a>
<a class="sourceLine" id="cb14-3" title="3"><span class="dt">int</span> <span class="fu">incr</span>(<span class="dt">int</span> x) {</a>
<a class="sourceLine" id="cb14-4" title="4">  <span class="kw">return</span> = x + <span class="dv">2</span>*y;</a>
<a class="sourceLine" id="cb14-5" title="5">}</a></code></pre></div>
<p>Comparing the post-condition on Line 2 with the method body, you might start to wonder whether you will now have to write everything twice, once "for real" and once for the specification. And indeed, there can be considerable overlap, sometimes to a frustrating extent. This could lead to the problem of doing the same mistakes twice: A program verifies in VerCors, thus you can be sure that it adheres to its specifications, but still it does not do what you want because the specifications contain the same error as the code. However, in many cases the specifications will be simpler than the executable code, avoiding the duplication of code and errors. Nevertheless, it can sometimes be useful to have one person write the specifications (e.g. the developer who wants to use the method), and another person writing the implementation.</p>
<h2 id="loop-invariants">Loop Invariants</h2>
<p>So far, we have looked at rather straight-forward programs that progress linearly from the top to the bottom. For them, it was rather easy to specify "if this holds at the top, then that holds at the bottom". But what if we use loops? Now we cannot know at the end of the program whether we did the loop once, 100 times or skipped it completely. How can we make assertions about such a program? The answer are <em>loop invariants</em>. A loop invariant is a statement that has to evaluate to <code>true</code> right before the loop is entered and at the end of each loop iteration. Thereby, we can be sure that after the loop, the loop invariant holds, no matter how often it was executed. In VerCors, loop invariants are specified right above the loop header using the keyword <code>loop_invariant</code>:</p>
<div class="sourceCode" id="cb15"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb15-1" title="1"><span class="co">//@ requires a&gt;0 &amp;&amp; b&gt;0;</span></a>
<a class="sourceLine" id="cb15-2" title="2"><span class="co">//@ ensures \result == a*b;</span></a>
<a class="sourceLine" id="cb15-3" title="3"><span class="kw">public</span> <span class="dt">int</span> <span class="fu">mult</span>(<span class="dt">int</span> a, <span class="dt">int</span> b) {</a>
<a class="sourceLine" id="cb15-4" title="4">  <span class="dt">int</span> res = <span class="dv">0</span>;</a>
<a class="sourceLine" id="cb15-5" title="5">  <span class="co">//@ loop_invariant res == i*a;</span></a>
<a class="sourceLine" id="cb15-6" title="6">  <span class="co">//@ loop_invariant i &lt;= b;</span></a>
<a class="sourceLine" id="cb15-7" title="7">  <span class="kw">for</span> (<span class="dt">int</span> i=<span class="dv">0</span>; i&lt;b; i++) {</a>
<a class="sourceLine" id="cb15-8" title="8">    res = res + a;</a>
<a class="sourceLine" id="cb15-9" title="9">  }</a>
<a class="sourceLine" id="cb15-10" title="10">  <span class="kw">return</span> res;</a>
<a class="sourceLine" id="cb15-11" title="11">}</a></code></pre></div>
<p>Let us examine why this program verifies. The first loop invariant holds before we start the loop, because we initialise both <code>i</code> and <code>res</code> to zero, and <code>0 == 0*a</code> is true. Then in each iteration, we increase <code>i</code> by one and <code>res</code> by <code>a</code>, so if <code>res == i*a</code> held before the iteration, then it will also hold afterwards. Note the clear similarity between analysing a method body, where we assume the pre-condition, analyse the body and try to assert the post-condition, and analysing a loop body, where we assume the invariant, analyse the body, and try to assert the invariant again. Looking at the second invariant, we see that it holds before the loop execution because <code>i</code> is initialised to zero and <code>b</code> is required to be greater than zero. To understand why the invariant holds after each iteration, we also have to consider the loop condition, <code>i&lt;b</code>. This condition has to be true at the beginning of the iteration, otherwise the loop would have stopped iterating. Since <code>i</code> and <code>b</code> are integers and <code>i&lt;b</code> at the beginning of the iteration, an increment of <code>i</code> by one during the iteration will ensure that at the end of the iteration, <code>i&lt;=b</code> still holds. Note that if <code>i</code> or <code>b</code> were floating point numbers, <code>i</code> might have gone from <code>b-0.5</code> to <code>b+0.5</code>, and we would not have been able to assert the loop invariant. Likewise, any increment by more than one could step over b. Only the combination of all these factors lets us assert the invariant at the end of the loop iteration. When the loop stops iterating, we know three things: Each of the two loop invariants holds, and the loop condition does no longer hold (otherwise we would still be iterating). Note that the combination of the second loop invariant and the negated loop condition, <code>i&lt;=b &amp;&amp; !(i&lt;b)</code>, ensures that <code>i==b</code>. Combining that with the first invariant ensures the post-condition of the method.</p>
<p>Remember that VerCors checks for partial correctness, so there is no check whether the loop actually stops iterating at some point. It just asserts that <em>if</em> the loop stops, then the post-condition holds.</p>
<p>Note that, because loop bodies are analysed similarly to method bodies, you need to treat heap variables the same: If you want to use them in the loop body, you have to explicitly allow accessing them by including permissions in the loop invariant. And if a loop invariant does not mention the value of a heap variable, VerCors has to assume that it can have any arbitrary value after executing the loop. Note that this does not apply to stack variables, so the primitive integer variables in our example do not require permissions.</p>
<p>Because it is a common issue that you need to specify the same permissions in the method's pre-condition, post-condition and loop invariants, there is a short-hand notation <code>context_everywhere</code>. It extends the semantics of <code>context</code>, so <code>context_everywhere expr;</code> will add <code>expr</code> to the pre- and post-condition (i.e. <code>requires expr; ensures expr;</code>) and as an invariant to <em>all</em> loops inside the method's body. So if you have multiple loops inside your method's body, be careful what you put inside <code>context_everywhere</code>: You might give a loop permission for a heap variable without realising it, and then wonder why you cannot assert anything about the variable's value after that loop, despite the loop and its explicitly given invariants not touching it. Since <code>context_everywhere</code> also adds the expression to the pre- and post-condition, it can only occur in a method contract (just like <code>requires</code>, <code>ensures</code> and <code>context</code>).</p>
<h2 id="ghost-code">Ghost Code</h2>
<p>Sometimes, it is useful to write actual code just for the sake of specifications, for example to declare a helper variable. Such code is called <em>ghost code</em>. It is written in the framework of specifications, so for most languages, that is within the <code>/*@</code> comments. As a direct consequence of that, the regular compiler is not aware of it, and the "real" code cannot reference e.g. ghost variables. Since the specification language extends the target language, any expression that is possible in the target language is also possible in ghost code, for example method definitions, variable declarations, loops, etc. Additionally, the ghost code can use the extensions that the specification language adds to the target language, so you can for instance declare a variable of type <code>seq&lt;T&gt;</code>, which is defined explicitly for specifications (see above). Like any specification, ghost code must not affect the state of the "real" program. For that reason, it is not possible to change the values of "regular" variables or call "regular" methods, as the latter may have side effects on the program state. However, it is possible to read from "regular" variables (e.g. create a ghost variable with the same value), if you have permission to access that variable. Also, it is allowed to affect the <em>ghost state</em>, i.e. the state of the ghost variables (e.g. change their value). So while calling a "regular" method is forbidden due to potential side effects on the program state, calling a ghost method is allowed, even if it has side effects on ghost variables. Therefore, it is important that you keep in your mind a clear separation between ghost state and "real" program state.</p>
<p>This is all rather abstract, so let's look at an example:</p>
<div class="sourceCode" id="cb16"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb16-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb16-2" title="2"><span class="co">  requires arr != null ** Perm(arr[*], read);</span></a>
<a class="sourceLine" id="cb16-3" title="3"><span class="co">@*/</span></a>
<a class="sourceLine" id="cb16-4" title="4"><span class="kw">public</span> <span class="dt">void</span> <span class="fu">whatever</span>(<span class="dt">int</span> x, <span class="dt">int</span> y, <span class="dt">int</span>[] arr) {</a>
<a class="sourceLine" id="cb16-5" title="5">  x = <span class="dv">2</span>;</a>
<a class="sourceLine" id="cb16-6" title="6">  <span class="co">//@ ghost int min = (x&lt;y ? x : y);</span></a>
<a class="sourceLine" id="cb16-7" title="7">  <span class="co">//@ assert (\forall int i; 0&lt;=i &amp;&amp; i&lt;arr.length; min&lt;=arr[i] &amp;&amp; arr[i] &lt; 2*min);</span></a>
<a class="sourceLine" id="cb16-8" title="8">  <span class="kw">if</span> (arr.<span class="fu">length</span> &gt; <span class="dv">0</span>) {</a>
<a class="sourceLine" id="cb16-9" title="9">    x = <span class="dv">4</span>;</a>
<a class="sourceLine" id="cb16-10" title="10">  }</a>
<a class="sourceLine" id="cb16-11" title="11">  <span class="co">/*@ ghost </span></a>
<a class="sourceLine" id="cb16-12" title="12"><span class="co">  if (min &lt; x) {</span></a>
<a class="sourceLine" id="cb16-13" title="13"><span class="co">    assert (\forall int i; 0&lt;=i &amp;&amp; i&lt;arr.length; min&lt;=arr[i] &amp;&amp; arr[i] &lt; 2*min);</span></a>
<a class="sourceLine" id="cb16-14" title="14"><span class="co">  }</span></a>
<a class="sourceLine" id="cb16-15" title="15"><span class="co">  @*/</span></a>
<a class="sourceLine" id="cb16-16" title="16">}</a>
<a class="sourceLine" id="cb16-17" title="17"></a></code></pre></div>
<p>As you can see, we introduce a ghost variable <code>min</code>, which captures minimum of the values of <code>x</code> and <code>y</code> at a specific point in the computation (after assigning 2 to <code>x</code>). We already know the <code>\old</code> keyword to reference the state at the beginning of a method, but referencing the state in the middle of the execution is more difficult. Thus, a helper variable is an easy solution. Using this helper, the quantified assertion becomes much easier to formulate and to understand. After the first <code>if</code>, we use a ghost <code>if</code> to check our previous assertion again, but only in certain cases. Note that we have changed the value of <code>x</code> inside the first <code>if</code>, but <code>min</code> was not changed and thus is still based on the earlier value of <code>x</code> (just like a regular variable would be). Instead of the ghost <code>if</code>, we could have also used an implication <code>assert boolExpr ==&gt; expr;</code>, but that would not showcase the use of ghost code ;)</p>
<h3 id="ghost-methods-and-functions">Ghost Methods and Functions</h3>
<p>You can use ghost code not only within regular methods: As mentioned above, ghost code supports everything the target language supports, so you can declare entire ghost methods. Here is an example of a ghost method:</p>
<div class="sourceCode" id="cb17"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb17-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb17-2" title="2"><span class="co">requires x &gt; 0;</span></a>
<a class="sourceLine" id="cb17-3" title="3"><span class="co">ghost static int cond_add(bool cond, int x, int y) {</span></a>
<a class="sourceLine" id="cb17-4" title="4"><span class="co">  if (cond) {</span></a>
<a class="sourceLine" id="cb17-5" title="5"><span class="co">    return x+y;</span></a>
<a class="sourceLine" id="cb17-6" title="6"><span class="co">  } else {</span></a>
<a class="sourceLine" id="cb17-7" title="7"><span class="co">    return x;</span></a>
<a class="sourceLine" id="cb17-8" title="8"><span class="co">  }</span></a>
<a class="sourceLine" id="cb17-9" title="9"><span class="co">}</span></a>
<a class="sourceLine" id="cb17-10" title="10"><span class="co">@*/</span></a>
<a class="sourceLine" id="cb17-11" title="11"></a>
<a class="sourceLine" id="cb17-12" title="12"><span class="co">//@ requires val1 &gt; 0 &amp;&amp; val2&gt;0 &amp;&amp; z==val1+val2;</span></a>
<a class="sourceLine" id="cb17-13" title="13"><span class="dt">void</span> <span class="fu">some_method</span>(<span class="dt">int</span> val1, <span class="dt">int</span> val2, <span class="dt">int</span> z) {</a>
<a class="sourceLine" id="cb17-14" title="14">  <span class="co">//@ ghost int z2 = cond_add(val2&gt;0, val1, val2);</span></a>
<a class="sourceLine" id="cb17-15" title="15">  <span class="co">//@ assert z == z2;</span></a>
<a class="sourceLine" id="cb17-16" title="16">}</a></code></pre></div>
<p>The conditional addition <code>cond_add</code> is defined as a ghost method. Otherwise, it looks like any normal method, including having a method contract (in this case, just a single precondition). Note that the precondition is not wrapped in the special comment <code>//@</code>, like the precondition of <code>some_method</code> is. This is due to the fact that the entire ghost method already is inside a special comment <code>/*@</code>, so no additional comment is needed. We then use this ghost method in the body of <code>some_method</code>. If <code>cond_add</code> were a regular method, this would not be allowed, due to the potential of side effects (even though this particular method does not have any side effects). Note that <code>some_method</code> has as part of its pre-condition <code>val&gt;0</code>. Without this, calling <code>cond_add</code> would result in a failed verification, as its pre-condition might not be fulfilled at the time of the call.</p>
<p><em>Pure functions</em> are, in a way, a special kind of ghost methods. They have the form <code>modifiers pure return_type name(args) = expr;</code>, where <code>modifiers</code> is a potentially empty set of modifiers like <code>public</code> or <code>static</code>, the <code>return_type</code> and <code>name</code> are exactly what they say, <code>args</code> is a potentially empty list of arguments, and <code>expr</code> is a single expression defining the return value of the function. So it looks like any method, except the additional keyword <code>pure</code> and the fact that the body is a single expression following an <code>=</code>, rather than a block of statements. The important distinction to normal methods, apart from the fact that they have just one expression, is that pure functions <em>must be without side-effects</em>, even on the ghost state. Thus, the body <code>expr</code> cannot contain for instance calls to methods or variable assignments. Another consequence is that, while regular methods must return permissions explicitly in the post-condition, pure functions automatically return all their permissions, and the post-condition must <em>not</em> include permissions (see next chapter on <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">Permissions</a>). Since the example ghost method above actually does not have side-effects, we can turn it into a pure function using a ternary expression:</p>
<div class="sourceCode" id="cb18"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb18-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb18-2" title="2"><span class="co">requires x &gt; 0;</span></a>
<a class="sourceLine" id="cb18-3" title="3"><span class="co">static pure int cond_add(bool cond, int x, int y) </span></a>
<a class="sourceLine" id="cb18-4" title="4"><span class="co">  = cond ? x+y : x;</span></a>
<a class="sourceLine" id="cb18-5" title="5"><span class="co">@*/</span></a></code></pre></div>
<p>Note that the <code>ghost</code> keyword is not used. It is only required when using a statement or declaration from the target language (e.g. <code>if</code>, method definitions, etc), <em>not</em> for specification-internal constructs like defining a pure function. However, calling the pure function as a stand-alone statement (e.g. invoking a lemma) is seen as a method call from the target language, and therefore needs the <code>ghost</code> keyword: <code>//@ ghost myLemma();</code>.</p>
<p>Remember that in VerCors, only the contract of a method is used to reason about the behaviour of a method call, the actual behaviour of the method body is not taken into account at this point. For functions, this restriction is not as strict: For simple functions like the one above, VerCors actually uses the "real" behaviour of the function to analyse the behaviour of a call to that function. Thus, the behaviour does not need to be specified explicitly in a post-condition, like it does for other methods. However, this only works for simple functions, and for example a recursive function may still need a post-condition specifying its behaviour.</p>
<h4 id="abstract-methods-and-functions">Abstract Methods and Functions</h4>
<p>Ghost functions and methods do not require a body. Sometimes, you are only interested in the assertions that a function or method gives in its post-condition, and do not care about the actual implementation. In such a case, you can omit the body, turning the method or function <em>abstract</em>. Given that method calls are usually reasoned about only based on the contract and not on the body, the call site does not see a difference between an abstract method and a "real" method. However, this removes the assurance that it is actually possible to derive the post-condition from the pre-condition by some computation. Consider a post-condition of <code>false</code>. The call site will simply assume that the method establishes its post-condition, and treat it as a known fact. Normally, verifying the method body will check that the post-condition is actually fulfilled; but for an abstract method, this check is missing. Since <code>false</code> implies everything, this can easily lead to unsoundness. Therefore, from the perspective of correctness, it is always better to have a body proving that the post-condition can actually be established based on the pre-condition.</p>
<p>However, making a method abstract relieves VerCors from the effort to check that the method body actually adheres to the contract. Therefore, if you modified parts of your code and want to re-run the analysis, this can be an interesting option to speed up the repeated analysis: You first check the part of your program that you will not touch with the respective bodies in place. Once that verifies and you are certain that the post-conditions are realistic, you can make the methods abstract while you focus on the rest of your program. Thereby, VerCors does not have to re-analyse those methods every time you change other parts of your code.</p>
<p>Syntactically, an abstract method or function has a semicolon in place of its body (similar to method declarations e.g. in C header files):</p>
<div class="sourceCode" id="cb19"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb19-1" title="1"><span class="co">//@ requires a&gt;0 &amp;&amp; b&gt;0;</span></a>
<a class="sourceLine" id="cb19-2" title="2"><span class="co">//@ ensures \result == a*b;</span></a>
<a class="sourceLine" id="cb19-3" title="3"><span class="kw">public</span> <span class="dt">int</span> <span class="fu">mult</span>(<span class="dt">int</span> a, <span class="dt">int</span> b);</a>
<a class="sourceLine" id="cb19-4" title="4"><span class="co">// commented out body for the sake of speeding up the analysis:</span></a>
<a class="sourceLine" id="cb19-5" title="5"><span class="co">//{</span></a>
<a class="sourceLine" id="cb19-6" title="6"><span class="co">//  int res = 0;</span></a>
<a class="sourceLine" id="cb19-7" title="7"><span class="co">//  //@ loop_invariant res == i*a;</span></a>
<a class="sourceLine" id="cb19-8" title="8"><span class="co">//  //@ loop_invariant i &lt;= b;</span></a>
<a class="sourceLine" id="cb19-9" title="9"><span class="co">//  for (int i=0; i&lt;b; i++) {</span></a>
<a class="sourceLine" id="cb19-10" title="10"><span class="co">//    res += a;</span></a>
<a class="sourceLine" id="cb19-11" title="11"><span class="co">//  }</span></a>
<a class="sourceLine" id="cb19-12" title="12"><span class="co">//  return res;</span></a>
<a class="sourceLine" id="cb19-13" title="13"><span class="co">//}</span></a>
<a class="sourceLine" id="cb19-14" title="14"></a>
<a class="sourceLine" id="cb19-15" title="15"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb19-16" title="16"><span class="co">requires a&gt;0 &amp;&amp; b&gt;0;</span></a>
<a class="sourceLine" id="cb19-17" title="17"><span class="co">ensures \result == a+b;</span></a>
<a class="sourceLine" id="cb19-18" title="18"><span class="co">public pure int add(int a, int b);</span></a>
<a class="sourceLine" id="cb19-19" title="19"><span class="co">@*/</span></a></code></pre></div>
<p>Note that VerCors can also work with abstract methods that are not ghost methods (like <code>mult</code> in the example above), but your compiler may complain about missing method definitions.</p>
<h4 id="inline-functions">Inline Functions</h4>
<p>Programmers, who are familiar with for example C, know about the existence and the power of <code>#define</code>: You can write an expression that occurs frequently in your code as a define and then use it as if it were a function. However, the pre-processor of the compiler replaces every call to that function with the respective expression, so no real function call occurs, reducing e.g. performance overhead. A similar concept in VerCors are <em>inline functions</em>: Before verifying the program, VerCors also replaces every call to an inline function with the function's body as if you had written out the body in place of the function call. Therefore, during the analysis, the "real" behaviour of the function is taken into account, rather than just the specification of the function's contract. However, inline functions are only possible if the body of the function is simple enough; for example a recursive function cannot be used in that way, otherwise there would be an infinite loop of replacing a function call with the body, which again contains a call. Inline functions are declared using the <code>inline</code> keyword.</p>
<div class="sourceCode" id="cb20"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb20-1" title="1"><span class="co">//@ pure inline int min(int x, int y) = (x&lt;y ? x : y);</span></a></code></pre></div>
<p>Note that the <code>inline</code> keyword is also used for inlining resources (see chapter on <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Resources-and-Predicates">resources</a>), where boolean conditions that often occur together (e.g. permissions for all the fields of an object of a certain type) are grouped together and are treated similarly to inline functions.</p>
<h3 id="ghost-parameters-and-results">Ghost Parameters and Results</h3>
<p>So far, we have seen ghost code extending the body of an existing method, and being an entire method. You can also extend the header of an existing method, by adding additional parameters and return values to a method. This is done by using the <code>given</code> and <code>yields</code> keywords in the method contract, respectively. To assign and retrieve the values when calling the method, use <code>with</code> and <code>then</code>, respectively:</p>
<div class="sourceCode" id="cb21"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb21-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb21-2" title="2"><span class="co">given int x;</span></a>
<a class="sourceLine" id="cb21-3" title="3"><span class="co">given int y;</span></a>
<a class="sourceLine" id="cb21-4" title="4"><span class="co">yields int modified_x;</span></a>
<a class="sourceLine" id="cb21-5" title="5"><span class="co">requires x &gt; 0;</span></a>
<a class="sourceLine" id="cb21-6" title="6"><span class="co">ensures modified_x &gt; 0;</span></a>
<a class="sourceLine" id="cb21-7" title="7"><span class="co">@*/</span></a>
<a class="sourceLine" id="cb21-8" title="8"><span class="dt">int</span> <span class="fu">some_method</span>(<span class="dt">boolean</span> real_arg) {</a>
<a class="sourceLine" id="cb21-9" title="9">  <span class="dt">int</span> res = <span class="dv">0</span>;</a>
<a class="sourceLine" id="cb21-10" title="10">  ...</a>
<a class="sourceLine" id="cb21-11" title="11">  <span class="co">//@ ghost modified_x = x + 1;</span></a>
<a class="sourceLine" id="cb21-12" title="12">  ...</a>
<a class="sourceLine" id="cb21-13" title="13">  <span class="kw">return</span> res;</a>
<a class="sourceLine" id="cb21-14" title="14">}</a>
<a class="sourceLine" id="cb21-15" title="15"></a>
<a class="sourceLine" id="cb21-16" title="16"><span class="dt">void</span> <span class="fu">other_method</span>() {</a>
<a class="sourceLine" id="cb21-17" title="17">  <span class="co">//@ ghost int some_ghost;</span></a>
<a class="sourceLine" id="cb21-18" title="18">  <span class="dt">int</span> some_result = <span class="fu">some_method</span>(<span class="kw">true</span>) <span class="co">/*@ with {y=3; x=2;} then {some_ghost=modified_x;} @*/</span>;</a>
<a class="sourceLine" id="cb21-19" title="19">}</a></code></pre></div>
<p>There are several points of interest in this example: Note that the pre- and post-condition of <code>some_method</code> can reference the ghost parameter and result just like normal parameters. However, as stated before, the method contract is processed in the order it is given, so the <code>given int x;</code> must appear before the <code>requires</code> that mentions <code>x</code>. If the ghost parameters and results are not just primitive integers, but heap objects, then permissions are needed to access them, just like with normal parameters and results (see <a href="https://github.com/utwente-fmt/vercors/wiki/Tutorial-Permissions">following chapter</a>). There is no explicit <code>return</code> statement for the ghost result; instead, the ghost result is whatever value the variable has when the method returns. Therefore, make sure when your method returns that you always have a defined value assigned to your ghost result variable! In <code>other_method</code>, the <code>with</code> keyword is followed by a block of statements that assign values to the ghost parameters. The parameters are named, so the assignment can be in any order, and need not adhere to the order in which the ghost parameters are defined. Make sure to assign a value to each ghost parameter! Otherwise, the method will be missing a parameter, just as if you called a method <code>void foo(int x)</code> as <code>foo();</code>. Similarly, the <code>then</code> keyword is followed by a block of statements that can use the ghost result, in particular storing their value in local variables. The respective local variable, <code>who_cares</code>, must be a ghost variable, otherwise you would affect the state of the "real" program. Both the <code>with</code> and the <code>then</code> are placed in a specification comment between the method call and the respective semicolon.</p>
<h1 id="permissions">Permissions</h1>
<p><em>This feature is supported for all languages.</em></p>
<p>This section discusses the basics of handling ownership using a simple toy example. Ownership is used to express whether a thread (or synchronization object) has access to a specific element on the heap. This access can be shared among multiple threads (or synchronization objects), which allows the threads to read the value of this element, or it can be unique to one, in which case the value can written to or read from. Permission annotations are used to express ownership. We start by considering the following simple example program, written in Java:</p>
<div class="sourceCode" id="cb22"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb22-1" title="1"><span class="kw">class</span> Counter {</a>
<a class="sourceLine" id="cb22-2" title="2">  <span class="kw">public</span> <span class="dt">int</span> val;</a>
<a class="sourceLine" id="cb22-3" title="3"></a>
<a class="sourceLine" id="cb22-4" title="4">  <span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb22-5" title="5">    <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb22-6" title="6">  }</a>
<a class="sourceLine" id="cb22-7" title="7">}</a></code></pre></div>
<p>If you wish, you can store this file as, say, <code>counter.java</code>, and try to run VerCors on this file by running <code>vct --silicon counter.java</code> in a console (assuming you have VerCors installed). This program currently does not contain any annotations, but we will eventually annotate the program to verify the following simple property: after calling the <code>incr</code> function, the value of <code>val</code> has been increased by an amount <code>n</code>. This can be expressed as a postcondition for the <code>incr</code> method: <code>ensures this.val == \old(this.val) + n</code>.</p>
<p>However, if you run VerCors on this example, as it is now, you will see that VerCors fails to verify the correctness of the program and reports an 'AssignmentFailed: InsufficientPermission' error, since the caller of the method has insufficient permission to access <code>this.val</code>. First observe that <code>this.val</code> is shared-memory; there may be other threads that also access the <code>val</code> field, since the field is public. In order to prevent other threads from accessing <code>this.val</code> while the calling thread is executing <code>incr</code>, we need to specify that threads may only call <code>c.incr</code> (on any object <code>c</code> that is an instance of <code>Counter</code>) when they have <em>write permission</em> for <code>c.val</code>:</p>
<div class="sourceCode" id="cb23"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb23-1" title="1"><span class="kw">class</span> Counter {</a>
<a class="sourceLine" id="cb23-2" title="2">  <span class="kw">public</span> <span class="dt">int</span> val;</a>
<a class="sourceLine" id="cb23-3" title="3"></a>
<a class="sourceLine" id="cb23-4" title="4">  <span class="co">/*@</span></a>
<a class="sourceLine" id="cb23-5" title="5"><span class="co">    requires Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb23-6" title="6"><span class="co">    ensures Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb23-7" title="7"><span class="co">    ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb23-8" title="8"><span class="co">  */</span></a>
<a class="sourceLine" id="cb23-9" title="9">  <span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb23-10" title="10">    <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb23-11" title="11">  }</a>
<a class="sourceLine" id="cb23-12" title="12">}</a></code></pre></div>
<p>We added three things to the counter program. The first is a <code>requires</code> clause, which is a precondition expressing that <code>incr</code> can only be called when the calling thread has permission to write to <code>val</code>. The second is an <code>ensures</code> clause, which is a postcondition expressing that, given that the <code>incr</code> function terminates (which is trivial in the above example), the function returns write permission for <code>val</code> to the thread that made the call to <code>incr</code>. The third is a postcondition that states that after <code>incr</code> has terminated, the value of <code>this.val</code> has indeed been increased by <code>n</code>. If you run this annotated program with VerCors, you will see that it now passes. The remainder of this section mostly focuses on how to use the <code>Perm</code> ownership predicates.</p>
<p>Observe that the clause <code>Perm(this.val, 1)</code> expresses write permission for <code>this.val</code>. Recall that VerCors has a very explicit notion of ownership, and that ownership is expressed via fractional permissions. The second argument to the <code>Perm</code> predicate is a <em>fractional permission</em>; a rational number <code>q</code> in the range <code>0 &lt; q &lt;= 1</code>. The ownership system in VerCors enforces that all permissions for a shared memory location together cannot exceed <code>1</code>. This implies that, if some thread has permission <code>1</code> for a shared-memory location at some point, then no other thread can have any permission predicate for that location at that point, for otherwise the total amount of permissions for that location exceeds <code>1</code> (since fractional permissions are strictly larger than <code>0</code>). For this reason, we refer to permission predicates of the form <code>Perm(o.f, 1)</code> as <em>write permissions</em>, and <code>Perm(o.f, q)</code> with <code>q &lt; 1</code> as <em>read permissions</em>. Threads are only allowed to read from shared memory if they have read permission for that shared memory, and may only write to shared memory if they have write permission. In the above example, the function <code>incr</code> both reads and writes <code>this.val</code>, which is fine: having write permission for a field implies having read permission for that field.</p>
<p>Let us now go a bit deeper into the ownership system of VerCors. If one has a permission predicate <code>Perm(o.f, q)</code>, then this predicate can be <em>split</em> into <code>Perm(o.f, q\2) ** Perm(o.f, q\2)</code>. Moreover, a formula of the form <code>Perm(o.f, q1) ** Perm(o.f, q2)</code> can be <em>merged</em> back into <code>Perm(o.f, q1 + q2)</code>. For example, if we change the program annotations as shown below, the program still verifies successfully:</p>
<div class="sourceCode" id="cb24"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb24-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb24-2" title="2"><span class="co">  requires Perm(this.val, 1\2) ** Perm(this.val, 1\2);</span></a>
<a class="sourceLine" id="cb24-3" title="3"><span class="co">  ensures Perm(this.val, 1\2) ** Perm(this.val, 1\2);</span></a>
<a class="sourceLine" id="cb24-4" title="4"><span class="co">  ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb24-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb24-6" title="6"><span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb24-7" title="7">  <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb24-8" title="8">}</a></code></pre></div>
<p>For splitting and merging we use the <code>**</code> operator, which is known as the separating conjunction, a connective from separation logic. A formula of the form <code>P ** Q</code> can be read as: "<code>P</code>, and <em>separately</em> <code>Q</code>", and comes somewhat close to the standard logical conjunction. In essence, <code>P ** Q</code> expresses that the subformulas <code>P</code> and <code>Q</code> both hold, and in addition, that all ownership resources in <code>P</code> and <code>Q</code> are together <em>disjoint</em>, meaning that all the permission components together do not exceed <code>1</code> for any field. Consider the formula <code>Perm(x.f, 1) ** Perm(y.f, 1)</code>. The permissions for a field <code>f</code> cannot exceed <code>1</code>, therefore we can deduce that <code>x != y</code>.</p>
<p>One may also try to verify the following alteration, which obviously does not pass, since write permission for <code>this.val</code> is needed, but only read permission is obtained via the precondition. VerCors will again give an 'InsufficientPermission' failure on this example.</p>
<div class="sourceCode" id="cb25"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb25-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb25-2" title="2"><span class="co">  requires Perm(this.val, 1\2);</span></a>
<a class="sourceLine" id="cb25-3" title="3"><span class="co">  ensures Perm(this.val, 1\2);</span></a>
<a class="sourceLine" id="cb25-4" title="4"><span class="co">  ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb25-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb25-6" title="6"><span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb25-7" title="7">  <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb25-8" title="8">}</a></code></pre></div>
<p>If you replace both ownership predicates for <code>Perm(this.val, 3/2)</code>, then the tool will report a 'MethodPreConditionUnsound: MethodPreConditionFalse' error because the precondition can then never by satisfied by any caller, since no thread can have permission greater than <code>1</code> for any shared-memory location. VerCors is a verification tool for <em>partial correctness</em>; if the precondition of a method cannot be satisfied because it is absurd, then the program trivially verifies. To illustrate this, try to change the precondition into <code>requires false</code> and see what happens when running VerCors. Note that VerCors does try to help the user identify these cases by showing a 'MethodPreConditionUnsound' if it can derive that the precondition is unsatisfiable. But one has to be a bit careful about the assumptions made on the program as preconditions.</p>
<h3 id="self-framing">Self-framing</h3>
<p>Consider the following variant on our program. Would this verify?</p>
<div class="sourceCode" id="cb26"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb26-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb26-2" title="2"><span class="co">  requires Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb26-3" title="3"><span class="co">  ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb26-4" title="4"><span class="co">*/</span></a>
<a class="sourceLine" id="cb26-5" title="5"><span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb26-6" title="6">  <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb26-7" title="7">}</a></code></pre></div>
<p>This program does not verify and gives an 'InsufficientPermission' failure when given to VerCors. The reason is that, also in the specifications one cannot read from shared-memory without the required permissions. In this program, the <code>ensures</code> clause accesses <code>this.val</code>, however no ownership for <code>this.val</code> is ensured by the <code>incr</code> method. Note that, without a notion of ownership, one cannot establish the postcondition: perhaps some other thread changed the contents of <code>this.val</code> while evaluating the postcondition. By having a notion of ownership, no other thread can change the contents of <code>this.val</code> while we evaluate the postcondition of the call, since no other threads can have resources to do so.</p>
<p>Moreover, the order of specifying permissions and functional properties does matter. For example, the following program also does not verify, even though it ensures enough permissions to establish the postcondition:</p>
<div class="sourceCode" id="cb27"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb27-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb27-2" title="2"><span class="co">  requires Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb27-3" title="3"><span class="co">  ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb27-4" title="4"><span class="co">  ensures Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb27-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb27-6" title="6"><span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb27-7" title="7">  <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb27-8" title="8">}</a></code></pre></div>
<p>VerCors enforces that shared-memory accesses are <em>framed</em> by ownership resources. Before accessing <code>this.val</code> in the first <code>ensures</code> clause, the permissions for <code>this.val</code> must already be known! In the program given above, the access to <code>this.val</code> in the postcondition is <em>not</em> framed by the ownership predicate: it comes too late.</p>
<h3 id="permission-leaks">Permission leaks</h3>
<p>So what about the following change? Can VerCors successfully verify the following program?</p>
<div class="sourceCode" id="cb28"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb28-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb28-2" title="2"><span class="co">  requires Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb28-3" title="3"><span class="co">  ensures Perm(this.val, 1\2);</span></a>
<a class="sourceLine" id="cb28-4" title="4"><span class="co">  ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb28-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb28-6" title="6"><span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb28-7" title="7">  <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb28-8" title="8">}</a></code></pre></div>
<p>VerCors is able to verify the example program given above. However, less permission for <code>this.val</code> is ensured then is required, meaning that any thread that calls <code>c.incr</code> will need to give up write permission for <code>c.val</code>, but only receives read permission back in return, after <code>incr</code> has terminated. So this example has a <em>permission leak</em>. Recall that threads need full permission in order to write to shared heap locations, so essentially, calling <code>c.incr</code> has the effect of losing the ability to ever write to <code>c.val</code> again. In some cases this can be problematic, while in other cases this can be helpful, as losing permissions in such a way causes shared-memory to become read-only, specification-wise. However, in the scenario given below the permission leak will prevent successful verification:</p>
<div class="sourceCode" id="cb29"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb29-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb29-2" title="2"><span class="co">  requires Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb29-3" title="3"><span class="co">  ensures Perm(this.val, 1\2);</span></a>
<a class="sourceLine" id="cb29-4" title="4"><span class="co">  ensures this.val == \old(this.val) + n;</span></a>
<a class="sourceLine" id="cb29-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb29-6" title="6"><span class="dt">void</span> <span class="fu">incr</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb29-7" title="7">  <span class="kw">this</span>.<span class="fu">val</span> = <span class="kw">this</span>.<span class="fu">val</span> + n;</a>
<a class="sourceLine" id="cb29-8" title="8">}</a>
<a class="sourceLine" id="cb29-9" title="9">  </a>
<a class="sourceLine" id="cb29-10" title="10"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb29-11" title="11"><span class="co">  requires Perm(this.val, 1);</span></a>
<a class="sourceLine" id="cb29-12" title="12"><span class="co">*/</span></a>
<a class="sourceLine" id="cb29-13" title="13"><span class="dt">void</span> <span class="fu">incr2</span>(<span class="dt">int</span> n) {</a>
<a class="sourceLine" id="cb29-14" title="14">  <span class="fu">incr</span>(n);</a>
<a class="sourceLine" id="cb29-15" title="15">  <span class="fu">incr</span>(n);</a>
<a class="sourceLine" id="cb29-16" title="16">}</a></code></pre></div>
<p>In the <code>incr2</code> method, the first call <code>incr(n)</code> will consume write permission for <code>this.val</code>, but only produce read permission in return. Therefore, the requirements of the second call <code>incr(n)</code> cannot be satisfied, which causes the verification to be unsuccessful.</p>
<h3 id="some-extra-notation">Some extra notation</h3>
<p>We end the section by mentioning some notational enhancements for handling permissions. Instead of writing <code>Perm(o.f, 1)</code>, one may also write <code>Perm(o.f, write)</code>, which is perhaps a more readable way to express write permission for <code>o.f</code>.</p>
<p>Similarly, one can write <code>Perm(o.f, read)</code> to express a non-zero read permission. Note that if this is used in a pre- and postcondition, it is not guaranteed to be the same amount of permissions. The underlying amount of permissions is an unspecified fraction and can therefore not be merged back into a write permission. This can be observed in the following program where the <code>assert</code> fails:</p>
<pre><code>class Counter {
    int val;

    /*@
    requires Perm(this.val, write);
    ensures Perm(this.val, write);
    ensures this.val == \old(this.val) + n;
    */
    void incr(int n) {
        int oldValue = getValue();
        //@ assert Perm(this.val, write);
        this.val = oldValue + n;
    }
    
    /*@
    requires Perm(this.val, read);
    ensures Perm(this.val, read);
    ensures \result == this.val;
    */
    int getValue() {
        return this.val;
    }
}
</code></pre>
<p><code>read</code> is mostly useful for specifying immutable data structures. One can also write <code>Value(o.f)</code> to express read permission to <code>o.f</code>, where the value of the fractional permission does not matter. Consequently, <code>Value</code> ownership predicates can be split indefinitely.</p>
<p>If you want to express that a thread has no ownership over a certain heap element, then one can use the keyword <code>none</code>, e.g. <code>Perm(o.f, none)</code>.</p>
<p>If you want to express permissions to multiple locations, one may use <code>\forall* vars; range; expr</code>. For example, <code>(\forall* int j; j &gt;= 0 &amp;&amp; j &lt; array.length; Perm(array[j], write)</code> denotes that the thread has write access to all elements in <code>array</code>. It is equivalent to writing <code>Perm(array[0], write) ** Perm(array[1], write) ** … ** Perm(array[array.length-1], write)</code>. Another way to specify permissions of all array elements is to use <code>Perm(array[*], write)</code> which is equivalent to the previous expression.</p>
<p>If you want to reason about the value that the variable refers to as well then you can use <code>PointsTo(var, p, val)</code> which denotes that you have permission <code>p</code>for variable <code>var</code> which has value <code>val</code>. It is similar to saying <code>Perm(var, p)</code> and <code>var == val</code>.</p>
<h1 id="openclcudagpgpu">OpenCL/Cuda/GPGPU</h1>
<p>TODO @Mohsen</p>
<h1 id="axiomatic-data-types">Axiomatic Data Types</h1>
<p>This page discusses the axiomatic data types (ADTs) that are supported by VerCors. Some of these ADTs like sequences and sets are natively supported by the Viper toolset, the main back-end of VerCors. ADTs that are not natively supported, like matrices, vectors, and option types, are specified as <em>domains</em> in the <code>config/prelude.sil</code> file (specified in the Silver language). During the translation steps in VerCors, the <code>SilverClassReduction</code> class includes all domains that are needed to verify the input program.</p>
<h3 id="bags">Bags</h3>
<p>The notation <code>bag&lt;T&gt;</code> is used by VerCors to denote the type of <em>bags</em> with elements of type <code>T</code>. Bags are immutable and similar to sets; in fact they are <em>multi-sets</em>, meaning that bags permit duplicate elements. For instance, unlike ordinary sets the multi-set <code>{1,1,2,4}</code> is not equal to <code>{1,2,4}</code>. An integer bag can be declared in VerCors using the following syntax:</p>
<div class="sourceCode" id="cb31"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb31-1" title="1">bag&lt;<span class="dt">int</span>&gt; b = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">4</span> };</a></code></pre></div>
<p>The notation <code>bag&lt;T&gt; { }</code> is used to denote an <em>empty bag</em> of type <code>T</code>. Given a bag <code>b</code>, the notation <code>|b|</code> is used to denote the <em>size</em> of <code>b</code>, that is, the number of elements in <code>b</code>. For example, one could verify:</p>
<div class="sourceCode" id="cb32"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb32-1" title="1">bag&lt;<span class="dt">int</span>&gt; b = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">4</span> };</a>
<a class="sourceLine" id="cb32-2" title="2">assert |b| == <span class="dv">4</span>;</a>
<a class="sourceLine" id="cb32-3" title="3">assert |bag&lt;<span class="dt">int</span>&gt; { <span class="dv">7</span>, <span class="dv">8</span> }| == <span class="dv">2</span>;</a></code></pre></div>
<p>Please note that in some languages (like Java), <code>|=</code> is an operator. It is therefore recommended to put spaces between the size operator and any subsequent comparison or implication (<code>==&gt;</code>), i.e. <code>|b| == n</code> instead of <code>|b|==n</code>. This avoids ambiguity for the parser.</p>
<h5 id="bag-multiplicity">Bag multiplicity</h5>
<p>Given an integer bag <code>b</code>, the notation <code>2 in b</code> can be used to test whether <code>2</code> occurs in <code>b</code> (similar to testing for set membership, see also the section on Sets). However, bags may contain duplicate elements. Therefore, <code>2 in b</code> does not give a boolean value (like with set membership), but instead yields an integer value denoting the number of occurrences of <code>2</code> in <code>b</code>. In other words, <code>2 in b</code> denotes the <em>multiplicity</em> of <code>2</code> in <code>b</code>. For example, one may verify:</p>
<div class="sourceCode" id="cb33"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb33-1" title="1">bag&lt;<span class="dt">int</span>&gt; b = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">2</span>, <span class="dv">2</span>, <span class="dv">4</span> };</a>
<a class="sourceLine" id="cb33-2" title="2"><span class="fu">assert</span> (<span class="dv">2</span> in b) == <span class="dv">3</span>;</a>
<a class="sourceLine" id="cb33-3" title="3"><span class="fu">assert</span> (<span class="dv">1</span> in b) == <span class="dv">1</span>;</a>
<a class="sourceLine" id="cb33-4" title="4"><span class="fu">assert</span> (<span class="dv">3</span> in b) == <span class="dv">0</span>;</a></code></pre></div>
<h5 id="bag-union">Bag union</h5>
<p>Given two bags <code>bag&lt;T&gt; b1</code> and <code>bag&lt;T&gt; b2</code> of the same type <code>T</code>, the <em>union</em> of <code>b1</code> and <code>b2</code> is written <code>b1 + b2</code> and denotes the bag containing all elements of <code>b1</code> and <code>b2</code> (and thereby allowing duplicates). For example:</p>
<div class="sourceCode" id="cb34"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb34-1" title="1">bag&lt;<span class="dt">int</span>&gt; b1 = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span> };</a>
<a class="sourceLine" id="cb34-2" title="2">bag&lt;<span class="dt">int</span>&gt; b2 = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb34-3" title="3">assert |b1 + b2| == <span class="dv">4</span>;</a>
<a class="sourceLine" id="cb34-4" title="4">assert b1 + b2 == bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb34-5" title="5"><span class="fu">assert</span> (<span class="dv">1</span> in b1 + b2) == <span class="dv">1</span>;</a>
<a class="sourceLine" id="cb34-6" title="6"><span class="fu">assert</span> (<span class="dv">2</span> in b1 + b2) == <span class="dv">2</span>;</a></code></pre></div>
<h5 id="bag-difference">Bag difference</h5>
<p>Given two bags <code>b1</code> and <code>b2</code> of the same type, the <em>bag difference</em> (i.e. the relative complement) of <code>b1</code> and <code>b2</code> is written <code>b1 - b2</code>. In other words, <code>b1 - b2</code> denotes the set <code>b1</code> with all elements occurring in <code>b2</code> removed (of course by keeping multiple occurrences of elements into account). For instance:</p>
<div class="sourceCode" id="cb35"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb35-1" title="1">bag&lt;<span class="dt">int</span>&gt; b1 = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb35-2" title="2">bag&lt;<span class="dt">int</span>&gt; b2 = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb35-3" title="3">assert b1 - b2 == bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span> };</a></code></pre></div>
<h5 id="bag-intersection">Bag intersection</h5>
<p>Given two bags <code>bag&lt;T&gt; b1</code> and <code>bag&lt;T&gt; b2</code> of equal type <code>T</code>, the <em>intersection</em> of <code>b1</code> and <code>b2</code> is written <code>b1 * b2</code> and denotes the bag containing all elements that occur both in <code>b1</code> and <code>b2</code> and thereby keeping multiplicity into account (unlike ordinary set intersection). As an example, one may verify the following program:</p>
<div class="sourceCode" id="cb36"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb36-1" title="1">bag&lt;<span class="dt">int</span>&gt; b1 = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb36-2" title="2">bag&lt;<span class="dt">int</span>&gt; b2 = bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">4</span> };</a>
<a class="sourceLine" id="cb36-3" title="3">assert b1 * b2 == bag&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span> };</a>
<a class="sourceLine" id="cb36-4" title="4">assert b1 * bag&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">2</span>, <span class="dv">4</span> } == bag&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">2</span> };</a></code></pre></div>
<h5 id="future-enhancements">Future enhancements</h5>
<p>Given a bag <code>b</code>, VerCors currently does not support shorthand notations for adding or removing elements from <code>b</code>, like <code>b += 3</code> and <code>b -= 2</code>. In essence, the notation <code>b += 3</code> can be rewritten to <code>b += bag&lt;int&gt; { 3 }</code> (by inferring the bag type, in this case <code>int</code>), which can in turn be defined as shorthand for <code>b = b + bag&lt;int&gt; { 3 }</code>. The same reduction pattern would apply for <code>-=</code>.</p>
<p>We could introduce the notation <code>v member b</code> as a shorthand for <code>(v in b) &gt; 0</code>, thereby returning a boolean result. We may even reuse the notation <code>v in b</code> for this, perhaps depending on the context.</p>
<h3 id="maps">Maps</h3>
<p>VerCors does not have support for maps yet. As a future enhancement we may implement support for maps, for example using the notation <code>map&lt;T,U&gt; m</code>, where <code>T</code> denotes the type of all <em>keys</em> of <code>m</code> (i.e. the type of the <em>domain</em> of <code>m</code>) and <code>U</code> denotes the type of the <em>values</em> of <code>m</code> (i.e. the codomain type).</p>
<h3 id="option-types">Option Types</h3>
<p>The notation <code>option&lt;T&gt;</code> is used to denote the <em>option data type</em>: the type that extends the given type <code>T</code> with an extra element named <code>None</code>. More specifically, elements of type <code>option&lt;T&gt;</code> are either <code>None</code> or <code>Some(e)</code>, where <code>e</code> is of type <code>T</code>. For example, the option integer type can be declared in VerCors using the following notation:</p>
<div class="sourceCode" id="cb37"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb37-1" title="1">option&lt;<span class="dt">int</span>&gt; field1 = None;</a>
<a class="sourceLine" id="cb37-2" title="2">option&lt;<span class="dt">int</span>&gt; field2 = <span class="fu">Some</span>(<span class="dv">3</span>);</a></code></pre></div>
<p>Axioms are provided so that <code>None != Some(e)</code> for every element <code>e</code>, and for every <code>e1</code> and <code>e2</code> we have <code>Some(e1) == Some(e1)</code> only if <code>e1 == e2</code>.</p>
<h3 id="sequences">Sequences</h3>
<p>VerCors uses the notation <code>seq&lt;T&gt;</code> to denote sequence types: the type of sequences with elements of type <code>T</code>. Sequences represent ordered lists and are immutable; once created they cannot be altered.</p>
<h5 id="construction">Construction</h5>
<p>Sequences of type <code>T</code> can be constructed using the notation <code>seq&lt;T&gt; { E_0, ..., E_n }</code>, where <code>E_i</code> is of type <code>T</code> for every <code>i</code>. For example, <code>seq&lt;int&gt; { 1, 2, 3 }</code> is the <em>constant sequence</em> of three elements: 1, 2, and 3. In PVL this sequence can be constructed by writing:</p>
<div class="sourceCode" id="cb38"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb38-1" title="1">seq&lt;<span class="dt">int</span>&gt; s = seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a></code></pre></div>
<p>Naturally, the notation <code>seq&lt;T&gt; { }</code> is used to denote the <em>empty sequence</em> of type <code>T</code>.</p>
<p>Two sequences <code>s1</code> and <code>s2</code> are <em>equal</em> if they are: of equal length, of equal type, and if their elements are equal pair-wise. To test for equality the standard operator for equality <code>s1 == s2</code> can be used. The notation <code>|s|</code> is used to obtain the <em>length</em> of the sequence <code>s</code>. As with bags, it is recommended to put a space between the length operator and any subsequent comparison or implication, to avoid ambiguity with the <code>|=</code> operator when the code is parsed. In PVL one may for example verify:</p>
<div class="sourceCode" id="cb39"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb39-1" title="1">seq&lt;<span class="dt">int</span>&gt; s = seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb39-2" title="2">assert |s| == <span class="dv">3</span>;</a>
<a class="sourceLine" id="cb39-3" title="3">assert |seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span>, <span class="dv">4</span> }| == <span class="dv">4</span>;</a></code></pre></div>
<h5 id="indexing">Indexing</h5>
<p>A sequence <code>seq&lt;T&gt; xs</code> may be <em>indexed</em> at position <code>i</code>, written <code>xs[i]</code>, to retrieve the <code>i</code>-th element of <code>xs</code>. For example, the following should verify:</p>
<div class="sourceCode" id="cb40"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb40-1" title="1">seq&lt;<span class="dt">int</span>&gt; xs = seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb40-2" title="2">assert xs[<span class="dv">0</span>] == <span class="dv">1</span>;</a>
<a class="sourceLine" id="cb40-3" title="3">assert xs[<span class="dv">2</span>] == <span class="dv">3</span>;</a></code></pre></div>
<p>However, the current version of VerCors is rather limited in its indexing support. For example, the line <code>assert seq&lt;int&gt; { 1, 2, 3 }[0] == 1</code> gives a syntax error. This is because the left-hand side <code>E1</code> of any indexing notation <code>E1[E2]</code> can not be an arbitrary expression of type <code>seq&lt;T&gt;</code>, but most be <em>exactly</em> a sequence of type <code>T</code>. To solve this, one can either declare the sequence explicitly, like in the code example given above, or define an auxiliary <code>get</code> function as given below, and write <code>get(seq&lt;int&gt; { 1, 2, 3 }, i)</code> instead.</p>
<div class="sourceCode" id="cb41"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb41-1" title="1"><span class="dt">static</span> pure <span class="dt">int</span> <span class="fu">get</span>(seq&lt;<span class="dt">int</span>&gt; xs, <span class="dt">int</span> n) = xs[n];</a></code></pre></div>
<h5 id="concatenation">Concatenation</h5>
<p>Two sequences <code>seq&lt;T&gt; s1</code> and <code>seq&lt;T&gt; s2</code> of the same type <code>T</code> can be <em>concatenated</em> by using the plus operator: <code>s1 + s2</code>. For example in PVL one may verify:</p>
<div class="sourceCode" id="cb42"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb42-1" title="1">seq&lt;<span class="dt">int</span>&gt; s1 = seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span> };</a>
<a class="sourceLine" id="cb42-2" title="2">seq&lt;<span class="dt">int</span>&gt; s2 = seq&lt;<span class="dt">int</span>&gt; { <span class="dv">3</span>, <span class="dv">4</span> };</a>
<a class="sourceLine" id="cb42-3" title="3">assert s1 + s2 == seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span>, <span class="dv">4</span> };</a></code></pre></div>
<p>Axioms are provided to make sequence concatenation associative, <code>s1 + (s2 + s3) == (s1 + s2) + s3</code>, and to make the empty sequence neutral in the sense that <code>s + seq&lt;T&gt; { } == s</code> and <code>seq&lt;T&gt; { } + s == s</code>.</p>
<h5 id="head-tail-notation">Head-tail notation</h5>
<p>VerCors has built-in functions for obtaining the <em>head</em> and <em>tail</em> of a sequence, which are named <code>head</code> and <code>tail</code>. The <code>head(s)</code> function simply gives the first element of <code>s</code>, provided that such an element exists, and <code>tail(s)</code> gives the tail sequence of <code>s</code>. For example:</p>
<div class="sourceCode" id="cb43"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb43-1" title="1">seq&lt;<span class="dt">int</span>&gt; s = seq&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb43-2" title="2">assert <span class="fu">head</span>(s) == <span class="dv">1</span>;</a>
<a class="sourceLine" id="cb43-3" title="3">assert <span class="fu">tail</span>(s) == seq&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span> };</a></code></pre></div>
<p>The head of an empty sequence is undefined. The tail of an empty sequence is empty:</p>
<div class="sourceCode" id="cb44"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb44-1" title="1">seq&lt;<span class="dt">int</span>&gt; s = seq&lt;<span class="dt">int</span>&gt; { };</a>
<a class="sourceLine" id="cb44-2" title="2">assert <span class="fu">tail</span>(s) == seq&lt;<span class="dt">int</span>&gt; { };</a></code></pre></div>
<h5 id="future-enhancements-1">Future enhancements</h5>
<p>There is currently no alternative notation to construct sequences from elements, like <code>[1,2,3]</code>, but this would be a nice enhancement.</p>
<p>VerCors does not yet permit slicing notations for sequences. For example, <code>s[2..]</code> should denote the subsequence of <code>s</code> starting from index 2. The Viper toolset currently supports these notations.</p>
<p>Another enhancement would be an append operator <code>::</code>, so that <code>2 :: s</code> would be equal to <code>seq&lt;int&gt; { 2 } + s</code>. Then <code>head(2 :: s) == 2</code> and <code>tail(2 :: s) == s</code>. Providing support for <code>::</code> does not seem that hard, merely a matter of syntactic transformation.</p>
<p>Like in Dafny we could give support for the <em>membership operators</em> <code>in</code> and <code>!in</code>, so that <code>v in s</code> equals <code>true</code> only if <code>v</code> is contained in <code>s</code> (and likewise <code>v !in s</code> only if <code>!(v in s)</code>).</p>
<h3 id="sets">Sets</h3>
<p>The notation <code>set&lt;T&gt;</code> is used by VerCors to denote the type of <em>sets</em> with elements of type <code>T</code>. Sets are immutable, orderless (meaning that the elements are not ordered), and do not allow duplicates (the sets <code>{1,1,2}</code> and <code>{1,2}</code> are equivalent). As an example, a set of integers can be declared as follows:</p>
<div class="sourceCode" id="cb45"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb45-1" title="1">set&lt;<span class="dt">int</span>&gt; s = set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a></code></pre></div>
<p>The <em>empty set</em> of type <code>T</code> is denoted <code>set&lt;T&gt; { }</code>, the <em>singleton set</em> is denoted <code>set&lt;T&gt; { e }</code> (with <code>e</code> of type <code>T</code>), et cetera. As said before, sets do not permit duplicate elements and are orderless:</p>
<div class="sourceCode" id="cb46"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb46-1" title="1">assert set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> } == set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb46-2" title="2">assert set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> } == set&lt;<span class="dt">int</span>&gt; { <span class="dv">3</span>, <span class="dv">2</span>, <span class="dv">1</span> };</a></code></pre></div>
<h5 id="set-membership">Set membership</h5>
<p>Given a set <code>set&lt;T&gt; s</code> of type <code>T</code> and an element <code>T e</code> (an element <code>e</code> of type <code>T</code>), we may test whether <code>e</code> is a <em>member</em> of <code>s</code> by writing <code>(e in s)</code>. The <code>in</code> operation yields a Boolean result when applied to sets, indicating whether the given element (e.g. <code>e</code>) occurs in the specified set (e.g. <code>s</code>). For example:</p>
<div class="sourceCode" id="cb47"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb47-1" title="1">set&lt;<span class="dt">int</span>&gt; s = set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb47-2" title="2"><span class="fu">assert</span> (<span class="dv">2</span> in s);</a>
<a class="sourceLine" id="cb47-3" title="3">assert !(<span class="dv">8</span> in s);</a></code></pre></div>
<h5 id="set-union">Set union</h5>
<p>The <em>union</em> of two sets <code>set&lt;T&gt; s1</code> and <code>set&lt;T&gt; s2</code> of the same type <code>T</code> is written <code>s1 + s2</code> and denotes the set containing all elements of both <code>s1</code> and <code>s2</code> (that is, without duplicates). For example:</p>
<div class="sourceCode" id="cb48"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb48-1" title="1">set&lt;<span class="dt">int</span>&gt; s1 = set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span> };</a>
<a class="sourceLine" id="cb48-2" title="2">set&lt;<span class="dt">int</span>&gt; s2 = set&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb48-3" title="3">assert s1 + s2 == set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a></code></pre></div>
<h5 id="set-difference">Set difference</h5>
<p>Given two sets <code>set&lt;T&gt; s1</code> and <code>set&lt;T&gt; s2</code> of the same type <code>T</code>, the <em>set difference</em> of <code>s1</code> and <code>s2</code> is written <code>s1 - s2</code> and denotes the set of all elements that are in <code>s1</code> but not in <code>s2</code>. A simple verification example is given below.</p>
<div class="sourceCode" id="cb49"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb49-1" title="1">set&lt;<span class="dt">int</span>&gt; s1 = set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span>, <span class="dv">4</span> };</a>
<a class="sourceLine" id="cb49-2" title="2">set&lt;<span class="dt">int</span>&gt; s2 = set&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span>, <span class="dv">5</span> };</a>
<a class="sourceLine" id="cb49-3" title="3">assert s1 - s2 == set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">4</span> };</a></code></pre></div>
<h5 id="set-intersection">Set intersection</h5>
<p>Given two sets <code>set&lt;T&gt; s1</code> and <code>set&lt;T&gt; s2</code> of the same type <code>T</code>, the <em>intersection</em> of <code>s1</code> and <code>s2</code> is written <code>s1 * s2</code> and denotes the set containing all elements that are in both <code>s1</code> and <code>s2</code>. For example:</p>
<div class="sourceCode" id="cb50"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb50-1" title="1">set&lt;<span class="dt">int</span>&gt; s1 = set&lt;<span class="dt">int</span>&gt; { <span class="dv">1</span>, <span class="dv">2</span>, <span class="dv">3</span> };</a>
<a class="sourceLine" id="cb50-2" title="2">set&lt;<span class="dt">int</span>&gt; s2 = set&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span>, <span class="dv">4</span> };</a>
<a class="sourceLine" id="cb50-3" title="3">assert s1 * s2 == set&lt;<span class="dt">int</span>&gt; { <span class="dv">2</span>, <span class="dv">3</span> };</a></code></pre></div>
<h5 id="future-enhancements-2">Future enhancements</h5>
<p>We do not have a <em>subset</em> notation yet. For example, given two sets <code>s1</code> and <code>s2</code> of equal type, VerCors might be extended with the notation <code>s1 &lt;= s2</code> to test whether <code>s1</code> is a subset of <code>s2</code>. In the same way, <code>s1 &lt; s2</code> might be used as notation for <em>strict subset</em> testing.</p>
<p>Another nice extension would be a notation for <em>set comprehension</em>. For example, the notation <code>set&lt;int&gt; { n | n &gt;= 0 }</code> might denote the set of all non-negative integers.</p>
<h1 id="arrays-and-pointers">Arrays and Pointers</h1>
<p><em>This tutorial explains how to specify arrays and pointers in vercors. Java and PVL support arrays, whereas C and the GPGPU frontends only support pointers. The tutorial assumes you are familiar with arrays and pointers already.</em></p>
<h2 id="array-permissions">Array permissions</h2>
<p>We have learned already how to specify ownership of variables on the heap. Arrays generalize this concept by treating each element of the array as a separate location on the heap. For example, you might specify:</p>
<div class="sourceCode" id="cb51"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb51-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb51-2" title="2"><span class="co">requires ar != null &amp;&amp; ar.length == 3;</span></a>
<a class="sourceLine" id="cb51-3" title="3"><span class="co">context Perm(ar[0], write) ** Perm(ar[1], write) ** Perm(ar[2], write);</span></a>
<a class="sourceLine" id="cb51-4" title="4"><span class="co">*/</span></a>
<a class="sourceLine" id="cb51-5" title="5"><span class="dt">void</span> <span class="fu">example1</span>(<span class="dt">int</span>[] ar);</a></code></pre></div>
<p>This means we require the length to be 3, and require and return permission over each of the elements of the array. Length is treated in a special way here: even though it is a "field", we do not need permission to read it, because the length of an array cannot be changed and it is baked into Vercors.</p>
<p>Of course writing a specific length and manually asserting permission over each location is cumbersome, so we can write a contract that accepts arrays of any length as such:</p>
<div class="sourceCode" id="cb52"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb52-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb52-2" title="2"><span class="co">requires ar != null;</span></a>
<a class="sourceLine" id="cb52-3" title="3"><span class="co">context (\forall* int i; 0 &lt;= i &amp;&amp; i &lt; ar.length; Perm(ar[i], write));</span></a>
<a class="sourceLine" id="cb52-4" title="4"><span class="co">*/</span></a>
<a class="sourceLine" id="cb52-5" title="5"><span class="dt">void</span> <span class="fu">example2</span>(<span class="dt">int</span>[] ar);</a></code></pre></div>
<p>As mentioned in the permissions tutorial, the permissions are combined with <code>**</code> to <code>Perm(ar[0], write) ** Perm(ar[1], write) ** … ** Perm(ar[ar.length-1], write)</code>. Please note the <code>*</code> after forall, which denotes that the body of the forall is combined with separating conjunction (<code>**</code>) and not boolean conjunction (<code>&amp;&amp;</code>).</p>
<p>As you might expect, we can also use forall to specify properties about the values of the array:</p>
<div class="sourceCode" id="cb53"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb53-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb53-2" title="2"><span class="co">requires ar != null;</span></a>
<a class="sourceLine" id="cb53-3" title="3"><span class="co">context (\forall* int i; 0 &lt;= i &amp;&amp; i &lt; ar.length; Perm(ar[i], write));</span></a>
<a class="sourceLine" id="cb53-4" title="4"><span class="co">ensures (\forall int i; 0 &lt;= &amp;&amp; i &lt; ar.length; ar[i] == i);</span></a>
<a class="sourceLine" id="cb53-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb53-6" title="6"><span class="dt">void</span> <span class="fu">identity</span>(<span class="dt">int</span>[] ar) {</a>
<a class="sourceLine" id="cb53-7" title="7">    <span class="kw">for</span>(<span class="dt">int</span> i = <span class="dv">0</span>; i &lt; ar.<span class="fu">length</span>; i++)</a>
<a class="sourceLine" id="cb53-8" title="8">    <span class="co">/*@</span></a>
<a class="sourceLine" id="cb53-9" title="9"><span class="co">    loop_invariant (\forall* int i; 0 &lt;= i &amp;&amp; i &lt; ar.length; Perm(ar[i], write));</span></a>
<a class="sourceLine" id="cb53-10" title="10"><span class="co">    loop_invariant (\forall int j; 0 &lt;= j &amp;&amp; j &lt; i; ar[j] == j); */</span></a>
<a class="sourceLine" id="cb53-11" title="11">    {</a>
<a class="sourceLine" id="cb53-12" title="12">        ar[i] = i;</a>
<a class="sourceLine" id="cb53-13" title="13">    }</a>
<a class="sourceLine" id="cb53-14" title="14">}</a></code></pre></div>
<h2 id="syntactic-sugar">Syntactic sugar</h2>
<p>Specifying arrays quickly leads to prefix a lot of statement with <code>\forall* int i; 0 &lt;= i &amp;&amp; i &lt; ar.length;</code>, so there is some syntactic sugar. First, you might want to use <code>context_everywhere</code> for the permission specification of the array, so that it is automatically propagates to loop invariants:</p>
<div class="sourceCode" id="cb54"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb54-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb54-2" title="2"><span class="co">context_everywhere (\forall* int i; 0 &lt;= i &amp;&amp; i &lt; ar.length; Perm(ar[i], write));</span></a>
<a class="sourceLine" id="cb54-3" title="3"><span class="co">ensures (\forall int i; 0 &lt;= &amp;&amp; i &lt; ar.length; ar[i] == i);</span></a>
<a class="sourceLine" id="cb54-4" title="4"><span class="co">*/</span></a>
<a class="sourceLine" id="cb54-5" title="5"><span class="dt">void</span> <span class="fu">identity</span>(<span class="dt">int</span>[] ar) {</a>
<a class="sourceLine" id="cb54-6" title="6">    <span class="kw">for</span>(<span class="dt">int</span> i = <span class="dv">0</span>; i &lt; ar.<span class="fu">length</span>; i++)</a>
<a class="sourceLine" id="cb54-7" title="7">    <span class="co">/*@</span></a>
<a class="sourceLine" id="cb54-8" title="8"><span class="co">    loop_invariant (\forall int j; 0 &lt;= j &amp;&amp; j &lt; i; ar[j] == j); */</span></a>
<a class="sourceLine" id="cb54-9" title="9">    {</a>
<a class="sourceLine" id="cb54-10" title="10">        ar[i] = i;</a>
<a class="sourceLine" id="cb54-11" title="11">    }</a>
<a class="sourceLine" id="cb54-12" title="12">}</a></code></pre></div>
<p>This whole forall* can also be written as <code>Perm(ar[*], write)</code>, which still means write permission over all the elements of the array:</p>
<div class="sourceCode" id="cb55"><pre class="sourceCode java"><code class="sourceCode java"><a class="sourceLine" id="cb55-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb55-2" title="2"><span class="co">requires ar != null;</span></a>
<a class="sourceLine" id="cb55-3" title="3"><span class="co">context_everywhere Perm(ar[*], write);</span></a>
<a class="sourceLine" id="cb55-4" title="4"><span class="co">ensures (\forall int i; 0 &lt;= &amp;&amp; i &lt; ar.length; ar[i] == i);</span></a>
<a class="sourceLine" id="cb55-5" title="5"><span class="co">*/</span></a>
<a class="sourceLine" id="cb55-6" title="6"><span class="dt">void</span> <span class="fu">identity</span>(<span class="dt">int</span>[] ar) {</a>
<a class="sourceLine" id="cb55-7" title="7">    <span class="kw">for</span>(<span class="dt">int</span> i = <span class="dv">0</span>; i &lt; ar.<span class="fu">length</span>; i++)</a>
<a class="sourceLine" id="cb55-8" title="8">    <span class="co">/*@</span></a>
<a class="sourceLine" id="cb55-9" title="9"><span class="co">    loop_invariant (\forall int j; 0 &lt;= j &amp;&amp; j &lt; i; ar[j] == j); */</span></a>
<a class="sourceLine" id="cb55-10" title="10">    {</a>
<a class="sourceLine" id="cb55-11" title="11">        ar[i] = i;</a>
<a class="sourceLine" id="cb55-12" title="12">    }</a>
<a class="sourceLine" id="cb55-13" title="13">}</a></code></pre></div>
<p>If you want to specify the length of an array, you can write as well: <code>\array(ar, N)</code> which is equivalent to <code>ar != null &amp;&amp; ar.length == N</code>. More interestingly, there is also <code>\matrix(mat, M, N)</code>, which is equivalent to:</p>
<pre><code>mat != null ** mat.length == M **
(\forall* int i; 0 &lt;= i &amp;&amp; i &lt; M; Perm(mat[i], read)) **
(\forall int i; 0 &lt;= i &amp;&amp; i &lt; M; mat[i].length == N) **
(\forall int i; 0 &lt;= i &amp;&amp; i &lt; M; (\forall int j; 0 &lt;= j &amp;&amp; j &lt; M &amp;&amp; mat[i] == mat[j]; i == j))
</code></pre>
<p>The last line is interesting here. In Java there is no such thing as a true matrix: instead we can make an array of arrays. However, there is nothing preventing you from putting the same row array instance in multiple rows. The last statement says that if we have valid row indices i, j, we know that <code>i != j ==&gt; mat[i] != mat[j]</code> (and the contrapositive).</p>
<h2 id="pointers">Pointers</h2>
<p>Pointers themselves are quite well-supported, but we don't support casting and structs in C, making the end result quite limited. For the support that we do have, pointers can be specified with two constructs: <code>\pointer</code> and <code>\pointer_index</code>.</p>
<p>We write <code>\pointer(p, size, perm)</code> to express that <code>p != NULL</code>, the pointer <code>p</code> is valid from (at least) <code>0</code> to <code>size-1</code>, and we assert <code>perm</code> permission over those locations.</p>
<p>We write <code>\pointer_index(p, index, perm)</code> to express that <code>p != NULL</code>, <code>(p+i)</code> is a valid location, and we have permission <code>perm</code> at that location.</p>
<div class="sourceCode" id="cb57"><pre class="sourceCode c"><code class="sourceCode c"><a class="sourceLine" id="cb57-1" title="1"><span class="co">/*@</span></a>
<a class="sourceLine" id="cb57-2" title="2"><span class="co">requires \pointer(a, 10, write);</span></a>
<a class="sourceLine" id="cb57-3" title="3"><span class="co">*/</span></a>
<a class="sourceLine" id="cb57-4" title="4"><span class="dt">void</span> test(<span class="dt">int</span> *a) {</a>
<a class="sourceLine" id="cb57-5" title="5">    <span class="co">//@ assert \pointer(a, 10, write);</span></a>
<a class="sourceLine" id="cb57-6" title="6">    <span class="co">//@ assert \pointer(a, 8, write);</span></a>
<a class="sourceLine" id="cb57-7" title="7">    <span class="co">//@ assert (\forall* int i; 0 &lt;= i &amp;&amp; i &lt; 10; \pointer_index(a, i, write));</span></a>
<a class="sourceLine" id="cb57-8" title="8">    <span class="dt">int</span> *b = a+<span class="dv">5</span>;</a>
<a class="sourceLine" id="cb57-9" title="9">    <span class="co">//@ assert a[5] == b[0];</span></a>
<a class="sourceLine" id="cb57-10" title="10">    <span class="co">//@ assert \pointer(b, 5, write);</span></a>
<a class="sourceLine" id="cb57-11" title="11">}</a></code></pre></div>
<h2 id="injectivity-object-arrays-and-efficient-verification">Injectivity, object arrays and efficient verification</h2>
<h2 id="internals">Internals</h2>
<h1 id="parallel-blocks">Parallel Blocks</h1>
<p>TODO @Mohsen (including barriers)</p>
<h1 id="atomics-and-locks">Atomics and Locks</h1>
<p>TODO @Raul (+invariants)</p>
<h1 id="models">Models</h1>
<p>TODO @Raul (Histories + Futures)</p>
<h1 id="resources-and-predicates">Resources and Predicates</h1>
<p>TODO @Bob</p>
<h1 id="inheritance">Inheritance</h1>
<p>TODO @Bob</p>
<h1 id="term-rewriting-rules">Term Rewriting Rules</h1>
<p>VerCors allows you to define your own term rewriting rules via <code>jspec</code> files. This chapter shows you how.</p>
<h1 id="magic-wands">Magic Wands</h1>
<p>The handling of magic wands is non-trivial, so this chapter takes a closer look at that. For further reading, see e.g. <em>"Witnessing the elimination of magic wands". Blom, S.; and Huisman, M. STTT, 17(6): 757–781. 2015</em>.</p>
<h1 id="inhale-and-exhale">Inhale and exhale</h1>
<p>// TODO: Explain inhale and exhale statements (add warning!)</p>
<h1 id="what-does-this-vercors-error-message-mean">What does this VerCors error message mean?</h1>
<p>This page is to explain what the different error messages in VerCors mean. We try to keep this page alphabetically ordered by error message (case insensitive sorting. For sentences: spaces come before letters in the alphabet). If you have a question about error messages, please add a question to this wiki page. If you know the answer to a question asked here, please replace the question with the answer (or with a link to the answer elsewhere on the wiki).</p>
<h4 id="assignmentfailed-insufficient-permission">AssignmentFailed: insufficient permission</h4>
<p>This means you are trying to write to a variable (at the left hand side of an assignment) to which VerCors cannot establish a write-permission. The statement <code>assert(Perm(lhs,1));</code> in which <code>lhs</code> is the left hand side of this assignment will fail at this point in your code, but should be provable.</p>
<h4 id="illegal-argument-count">Illegal argument count</h4>
<p>This is a internal VerCors error, that might be due to a parse-error in your script. We have opened a issue for this <a href="https://github.com/utwente-fmt/vercors/issues/125">here</a>.</p>
<h4 id="illegal-iteration-invariant">Illegal iteration invariant</h4>
<p>This seems to happen when you try to specify a <code>loop_invariant</code> in a <code>for</code>-loop, where you're using a resource as invariant. Try using <code>context</code> (short for <code>ensures</code> and <code>requires</code> combined) instead. Are you getting this message in a different situation? Do let us know!</p>
<h4 id="javalang">java.lang.*</h4>
<p>This is never supposed to happen, but unfortunately, it did. Thank you for finding this bug in VerCors. Try searching our issue tracker to see if you believe this bug is already being addressed. If not, please let us know about it by adding it!</p>
<h4 id="no-viable-alternative-at-">No viable alternative at ...</h4>
<p>This is a parsing error, you may have made a typo, or inadvertently used a reserved keyword as a variable. Check your code at the position indicated. If this is the start of a line, make sure there is a semicolon <code>;</code> at the previous line.</p>
<h4 id="notwellformedinsufficientpermission">NotWellFormed:InsufficientPermission</h4>
<p>This error is shown at a specification. We require rules to be 'self framing', this means that you need read-permission in order to access variables, even inside specifications. Furthermore, checks like array accesses being within bounds need to hold. The order of the specifications makes a difference here: the lines of your specifications are checked from top to bottom, and from left to right. In order to establish permissions and bounds, add a line of specification before the line that gives you trouble, asserting that you have read permission, or that the access is within bounds.</p>
<p>If you see this error in the <code>\old(..)</code> part of an ensures expression, you need permission to that variable before executing the function. For constructors (e.g. <code>foo</code> method of class <code>foo</code>), there is no <code>\old(this.x)</code>, as the variable <code>this.x</code> is only created when the constructor is called.</p>
<h4 id="pre-condition-of-constructor-may-not-refer-to-this">Pre-condition of constructor may not refer to this</h4>
<p>When calling the constructor method of a class, the class variables are not yet initialised. Therefore, you should not refer to them. Do you need write-permission? Don't worry, the class constructor already has it by definition (as it implicitly creates the variables)!</p>
<h4 id="type-of-leftright-argument-is-resource-rather-than-boolean">Type of left/right argument is Resource rather than Boolean:</h4>
<p>This is a type error. You are using a symbol like <code>&amp;&amp;</code> that requires boolean values on both sides. Changing <code>&amp;&amp;</code> to <code>**</code>, or breaking up your specification into multiple lines might solve the problem for you.</p>
<h1 id="pvl-syntax-reference">PVL Syntax Reference</h1>
<p>On this page you can find a description of the syntax of PVL, Prototypal Verification Language; one of the languages for which VerCors supports verification.</p>
<h2 id="general">General</h2>
<ul>
<li><strong>Identifiers</strong> can consist of the characters a-z, A-Z, 0-9 and _. However, they must <strong>start</strong> with a letter (a-z, A-Z). Note that the following words are reserved and can therefore <strong>not</strong> be used as identifiers: create, action, destroy, send, recv, use, open, close, atomic, from, merge, split, process, apply, label, \result, write, read, none, empty and current_thread.</li>
<li>The keyword <code>this</code> is used to refer to the current object.</li>
<li>A program is typically defined within a <code>class</code>:</li>
</ul>
<pre><code>class ClassName {
    // Here you can add any constructors, class variables and method declarations
}
</code></pre>
<ul>
<li>You can write single-line or multi-line comments as follows:</li>
</ul>
<pre><code>// This is a single-line comment
/* 
    This is a 
    multi-line comment 
*/
</code></pre>
<ul>
<li>Lines in PVL should end with a semi-colon <code>;</code>.</li>
<li>Unlike JML, PVL specifications are <strong>not</strong> written in comments!</li>
</ul>
<h2 id="types-and-data-structures">Types and Data Structures</h2>
<table>
<thead>
<tr class="header">
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>int</code></td>
<td>Integer</td>
</tr>
<tr class="even">
<td><code>boolean</code></td>
<td><code>true</code> or <code>false</code></td>
</tr>
<tr class="odd">
<td><code>void</code></td>
<td>Used when a method does not return anything</td>
</tr>
<tr class="even">
<td><code>resource</code></td>
<td>Boolean-like type that also allows reasoning about permissions</td>
</tr>
<tr class="odd">
<td><code>frac</code></td>
<td>Fraction</td>
</tr>
<tr class="even">
<td><code>zfrac</code></td>
<td>Fraction that can also be zero.</td>
</tr>
<tr class="odd">
<td><code>process</code></td>
<td>Type of actions and defined processes in histories. For more information on histories &amp; futures, have a look at the section on <a href="#historiesAndFutures">Histories &amp; Futures</a>.</td>
</tr>
<tr class="even">
<td><code>T[]</code></td>
<td>Array which contains elements of type <code>T</code>. <code>T</code> should be replaced by a type. Note that when you initialize a new array, you should always define the length of the array, e.g. <code>new int[3]</code> instead of <code>new int[]</code>.</td>
</tr>
<tr class="odd">
<td><code>seq&lt;T&gt; var</code></td>
<td>Defines an immutable ordered list (sequence) named <code>var</code>. <code>T</code> should be replaced by a type.</td>
</tr>
<tr class="even">
<td><code>set&lt;T&gt; var</code></td>
<td>Defines an immutable orderless collection (set) that does not allow duplicates. <code>T</code> should be replaced by a type.</td>
</tr>
<tr class="odd">
<td><code>bag&lt;T&gt; var</code></td>
<td>Defines an immutable orderless collection (bag) that does allow duplicates. <code>T</code> should be replaced by a type.</td>
</tr>
<tr class="even">
<td><code>option&lt;T&gt;</code></td>
<td>Extends type <code>T</code> with an extra element <code>None</code>. Each element is then either of the type <code>None</code> or of the type <code>Some(e)</code> where <code>e</code> is of type <code>T</code>. <code>T</code> should be replaced by a type. Options cannot be unpacked at the moment.</td>
</tr>
</tbody>
</table>
<p>For more information on sequences, sets, bags and options, have a look at the wiki page on <a href="https://github.com/utwente-fmt/vercors/wiki/Axiomatic-Data-Types">Axiomatic Data Types</a>.</p>
<h2 id="expressions">Expressions</h2>
<h3 id="infix-operators">Infix Operators</h3>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>==</code>, <code>!=</code></td>
<td>Equals and not equals for reasoning about the equality of expressions</td>
</tr>
<tr class="even">
<td><code>&amp;&amp;</code>, <code>||</code>, <code>!</code></td>
<td>And, or, and negation respectively for reasoning with boolean variables.</td>
</tr>
<tr class="odd">
<td><code>&lt;</code>, <code>&lt;=</code>, <code>&gt;</code>, <code>&gt;=</code></td>
<td>Smaller than, smaller than equals, greater than and greater than equals respectively. They are used to compare integers.</td>
</tr>
<tr class="even">
<td><code>+</code>, <code>-</code>, <code>*</code>, <code>/</code>, <code>\</code></td>
<td>Addition, subtraction, multiplication, integer division and fractional division respectively.</td>
</tr>
<tr class="odd">
<td><code>++</code>, <code>--</code></td>
<td>Increase by 1 and decrease by 1 respectively. Unlike the other operators, this only requires an variable on the left-hand side.</td>
</tr>
<tr class="even">
<td><code>==&gt;</code></td>
<td>Implication. This statement evaluates to true unless the statement before the arrow evaluates to true and the statement after the arrow evaluates to false.</td>
</tr>
<tr class="odd">
<td><code>**</code></td>
<td>Separating conjunction. <code>a ** b</code> denotes that <code>a</code> and <code>b</code>point to different variables on the heap and that both expressions mus tevaluate to true. This is used to reason about multiple resources.</td>
</tr>
<tr class="even">
<td><code>-*</code></td>
<td>Magic wand or separating implication. This is used to reason about resources. //TODO: investigate what is supported.</td>
</tr>
<tr class="odd">
<td><code>new T()</code></td>
<td>Creates a new object of type <code>T</code>.</td>
</tr>
<tr class="even">
<td><code>new T[length]</code></td>
<td>Creates a new array which contains objects of type <code>T</code> with <code>length</code> number of items.</td>
</tr>
<tr class="odd">
<td><code>boolExpr ? exprA : exprB;</code></td>
<td>Evaluates <code>boolExpr</code>, if this is true it will return <code>exprA</code> and otherwise <code>exprB</code>.</td>
</tr>
</tbody>
</table>
<h3 id="quantified-expressions">Quantified Expressions</h3>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>(\forall vars; range; boolExpr)</code></td>
<td>Construct that allows you to repeat a certain expression for several variables. For example, <code>(\forall int j; j &gt;= 0 &amp;&amp; j &lt; n; array[j] &gt;= 0)</code> denotes that all elements in <code>array</code> nonnegative. It is equal to the following statement: <code>array[0] &gt;= 0 &amp;&amp; array[1] &gt;= 0 &amp;&amp; ... &amp;&amp; array[n-1] &gt;= 0</code>. <code>vars</code> should declare the variables over which we will reason. <code>range</code> can be any boolean expression, often used to describe the range of <code>vars</code>. <code>boolExpr</code> is some expression that should evaluate to a boolean for all variables in the given range.</td>
</tr>
<tr class="even">
<td><code>(\forall* vars; range; expr)</code></td>
<td>Similar construct to the <code>\forall</code> except that the expressions are separated by <code>**</code> instead of <code>&amp;&amp;</code>. One can for example write <code>(\forall* int j; j &gt;= 0 &amp;&amp; j &lt; array.length; Perm(array[j], write)</code> which denotes that the thread has writing access to all elements in <code>array</code>.</td>
</tr>
<tr class="odd">
<td><code>(\exists Type id; range; expr)</code></td>
<td>Evaluates to true if there exists an element, called <code>id</code>, such that the final expression evaluates to true.</td>
</tr>
<tr class="even">
<td><code>array[*]</code></td>
<td>This is a simplified <code>\forall*</code> expression that ranges over all elements in the array <code>array</code>. Instead of the example mentioned above for <code>\forall*</code>, you can then write <code>Perm(array[*], write)</code>. This <strong>cannot</strong> be used within nested <code>\forall*</code> expressions.</td>
</tr>
</tbody>
</table>
<h3 id="specification-only-expressions">Specification-only Expressions</h3>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>\result</code></td>
<td>Keyword that refers to the object that the method returns</td>
</tr>
<tr class="even">
<td><code>\old(expr)</code></td>
<td>Refers to the value of the specified expression in the pre-state. This can be used in a postcondition (ensures) or loop invariant in which case the pre-state is when the method was called.</td>
</tr>
<tr class="odd">
<td><code>held(x)</code></td>
<td>Check whether you are holding a non-reentrant lock. <code>x</code> should refer to the lock invariant. See also: <a href="https://github.com/utwente-fmt/vercors/blob/dev/examples/waitnotify/Queue.pvl">Queue.pvl</a>.</td>
</tr>
<tr class="even">
<td><code>idle(t)</code></td>
<td>Returns true if thread <code>t</code> is idle (before calling <code>t.fork()</code> or after calling <code>t.join()</code>). Overall a thread starts as idle, if the thread is forked, it goes to a 'runnning' state. If join is called on a running thread, then it goes back to an 'idle' state.</td>
</tr>
<tr class="odd">
<td><code>running(t)</code></td>
<td>Returns true if thread <code>t</code> is running. Overall a thread can go through the following states: idle --[t.fork()]--&gt; running --[t.join()]--&gt; idle</td>
</tr>
</tbody>
</table>
<h3 id="resources">Resources</h3>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>Perm(var, p)</code></td>
<td>Defines permissions for variable <code>var</code>. If <code>p</code> is <code>1</code> or <code>write</code> then it denotes write permission, anything between 0 and 1 or <code>read</code> denotes read permission. Be aware that you cannot use arithmetic when using <code>read</code> such as <code>read/2</code> or dividing <code>read</code> among multiple threads! <code>Perm()</code> is of the type Resource.</td>
</tr>
<tr class="even">
<td><code>PointsTo(var, p, val)</code></td>
<td>Denotes permissions <code>p</code> for variable <code>var</code> similar to <code>Perm()</code>. Moreover, variable <code>var</code> points to the value <code>val</code>. <code>PointsTo()</code> is of the type Resource.</td>
</tr>
<tr class="odd">
<td><code>Value(var)</code></td>
<td>Defines a read-only permission on the given variable. This read-only permission cannot become a write permission and it can duplicate as often as necessary.</td>
</tr>
</tbody>
</table>
<h2 id="control-flow-constructs">Control flow constructs</h2>
<table>
<thead>
<tr class="header">
<th>Control flow construct</th>
<th>Example</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td>Function</td>
<td><code>contract returnType functionName(paramType paramName, ...) { ... }</code>. <code>contract</code> should describe the specification of the method. <code>returnType</code> should be replaced by a specific type or <code>void</code> depending on what (if anything) the method returns. <code>functionName</code> should be replaced by the name of the function. <code>paramType paramName, ...</code> is a comma-separated list of parameters, for every parameter you should define its type and its name. It is also possible to have no parameters. For example: <code>ensures \result == a + b; int sum(int a, int b) { return a + b; }</code> is a function which returns an integer that is the sum of the parameters given.<br> <strong>Pure and Abstract Functions:</strong> Pure functions are declared by prepending modifiers <code>static</code> and <code>pure</code> in that order. They should be side-effect free and their postconditions must not contain resource assertions such as accessibility predicates. All accessibility constraints for the body of the function and for the postcondition should be ensured by the preconditions. In fact, since pure functions applications are side-effect-free, pre- and post-states of a function application are the same. <em>Example:</em><br><code>requires a != null;</code><br><code>requires 0 &lt;= n &amp;&amp; n &lt;= a.length;</code><br><code>requires (\forall* int j; 0 &lt;= j &amp;&amp; j &lt; a.length; Perm(a[j],1\2));</code><br><code>ensures (n==a.length)? \result == 0 : \result == a[n] + sumAll(a,n+1);</code><br><code>static pure int sumAll(int []a, int n);</code><br>Notice that in the example the function has no body. By doing so we declare an <em>abstract function</em>. When calling this function, its pre-conditions will be checked and its postconditions assumed. No correspondence should be assumed between pure and abstract functions.</td>
</tr>
<tr class="even">
<td>Return</td>
<td><code>return</code> can be used to exit the current method. <code>return expr</code> can be used within a method to return a specific object as a result.</td>
</tr>
<tr class="odd">
<td>If-statement</td>
<td><ul><li>Single-line option: <code>if (boolExpr) ...;</code></li><li>Multi-line option: <code>if (boolExpr) { ... }</code></li></ul></td>
</tr>
<tr class="even">
<td>If-then-else</td>
<td><code>if (boolExpr) { ... } else { ... }</code></td>
</tr>
<tr class="odd">
<td>For-loop</td>
<td><code>loop_invariant p; for (...) { ... }</code></td>
</tr>
<tr class="even">
<td>While-loop</td>
<td><code>loop_invariant p; while (boolExpr) { ... }</code></td>
</tr>
<tr class="odd">
<td>Parallel block</td>
<td><code>par contract { ... }</code> OR <code>par identifier(iters) contract { ... }</code>. The <code>identifier</code> is optional and <code>iters</code> can be empty. <code>iters</code> specifies what variables are iterated over. Note that you can also extend a parallel block with another parallel block as follows: <code>par contract { ... } and contract { ... }</code> OR <code>par identifier(iters) contract { ... } and identifier(iters) contract { ... }</code>.</td>
</tr>
<tr class="even">
<td>Vector block</td>
<td><code>vec (iters) { ... }</code> is a variant of the parallel block where every step is executed in lock step. You do not need to specify a pre- and postcondition. <code>iters</code> should define what variables are iterated over, e.g., <code>int i = 0..10</code>.</td>
</tr>
<tr class="odd">
<td>Atomic block</td>
<td><code>atomic(inv) { ... }</code> performs the actions within the block atomically. As a result, other threads will not be able to see any intermediate results, only the result before or after executing the atomic block. <code>inv</code> refers to an invariant which stores permissions that are necessary when executing the atomic block.</td>
</tr>
<tr class="even">
<td>Barrier</td>
<td><code>barrier(identifier) { contract }</code> waits for all threads to reach this point in the program, then permissions can be redistributed amongst all threads, as specified in the contract, after which the threads are allowed to continue. The barrier needs to know how many threads should reach it before everyone is allowed to continue, this is done by specifying <code>identifier</code> which refers to a parallel block before the barrier.</td>
</tr>
<tr class="odd">
<td>Fork a thread</td>
<td><code>fork expr</code> starts a new thread.</td>
</tr>
<tr class="even">
<td>Join a thread</td>
<td><code>join expr</code> waits for the specified thread to complete.</td>
</tr>
<tr class="odd">
<td>Wait</td>
<td><code>wait expr</code> will pause the thread until it is notified to continue.</td>
</tr>
<tr class="even">
<td>Notify</td>
<td><code>notify expr</code> will notify another thread that it may continue if it is waiting.</td>
</tr>
<tr class="odd">
<td>Acquire a lock</td>
<td><code> lock expr</code></td>
</tr>
<tr class="even">
<td>Release a lock</td>
<td><code>unlock expr</code></td>
</tr>
<tr class="odd">
<td>Label</td>
<td><code>label l</code> indicates a location in the program that can be jumped to with <code>goto l</code>.</td>
</tr>
<tr class="even">
<td>Goto</td>
<td><code>goto l</code> indicates control flow must be transferred to the location of the label <code>l</code>.</td>
</tr>
</tbody>
</table>
<p>Note that <strong>for-loops</strong> and <strong>while-loops</strong> should be <strong>preceded with a contract</strong> consisting of one or more loop invariants. Parallel blocks require a contract in the form of requires/ensures clauses.</p>
<h2 id="verification-flow-constructs">Verification flow constructs</h2>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>assert expr</code></td>
<td>Defines an assert statement <code>expr</code> which describes a condition that should hold at the program point where this statement is defined.</td>
</tr>
<tr class="even">
<td><code>assume expr</code></td>
<td>Defines a statement that assumes that <code>expr</code> holds. It can be put anywhere within the code.</td>
</tr>
<tr class="odd">
<td><code>requires expr</code></td>
<td>Defines the precondition as <code>expr</code>, i.e., <code>expr</code> must hold when calling this method. The precondition should be specified <strong>before</strong> the method declaration.</td>
</tr>
<tr class="even">
<td><code>ensures expr</code></td>
<td>Defines the postcondition as <code>expr</code>, i.e., <code>expr</code> must hold when completing this method. The postcondition should be specified <strong>before</strong> the method declaration.</td>
</tr>
<tr class="odd">
<td><code>context expr</code></td>
<td>This is an abbreviation that combines the statements <code>requires expr</code> and <code>ensures expr</code>. This statement should be specified <strong>before</strong> the method declaration.</td>
</tr>
<tr class="even">
<td><code>loop_invariant expr</code></td>
<td>Defines a loop invariant <code>expr</code> which is a condition that must hold when entering the loop and after each loop iteration. A loop invariant should be specified <strong>before</strong> the corresponding loop.</td>
</tr>
<tr class="odd">
<td><code>context_everywhere expr</code></td>
<td>This is an abbreviation that combines the statement <code>requires expr</code> and <code>ensures expr</code>. Moreover, it also adds <code>loop_invariant expr</code> to all loops within the method. This statement should be specified <strong>before</strong> the method declaration.</td>
</tr>
<tr class="even">
<td><code>given T p</code></td>
<td>Defines that a ghost input parameter (specification-only parameter) of type <code>T</code> with the name <code>p</code> is passed when this method is called. This statement should be specified <strong>before</strong> the method declaration.</td>
</tr>
<tr class="odd">
<td><code>yields x</code></td>
<td>Returns a ghost output parameter (specification-only parameter) to the callee of this method. This statement should be specified <strong>before</strong> the method declaration.</td>
</tr>
<tr class="even">
<td><code>with ... then ...</code></td>
<td><code>with</code> is used to pass a parameter to a method (which has a given statement) and <code>then</code> can be used to store a returned value from a <code>yields</code> statement. This statement is specified after the corresponding method call (on the same line) where you want to pass the parameter. All the <code>...</code> should be replaced by an assignment.</td>
</tr>
<tr class="odd">
<td><code>unfolding ... in expr</code></td>
<td>Temporarily unfold definitions in (pure) expressions. The <code>...</code> should be replaced by a predicate.</td>
</tr>
<tr class="even">
<td><code>refute expr</code></td>
<td>Disproves <code>expr</code> at the given program point. This statement can be put anywhere in the code. Internally it is translated to <code>assert !(expr)</code>.</td>
</tr>
<tr class="odd">
<td><code>inhale p</code></td>
<td>Take in the specified permissions and properties.</td>
</tr>
<tr class="even">
<td><code>exhale p</code></td>
<td>Discard the specified permissions</td>
</tr>
<tr class="odd">
<td><code>fold x</code></td>
<td>Wrap permissions inside the definition</td>
</tr>
<tr class="even">
<td><code>unfold x</code></td>
<td>Unwrap a bundle of permissions</td>
</tr>
<tr class="odd">
<td><code>witness x</code></td>
<td>Declares witness names. <em>This feature is deprecated and should no longer be used</em>.</td>
</tr>
</tbody>
</table>
<h3 id="histories--futures-">Histories &amp; Futures <a name="historiesAndFutures"></h3>
<h4 id="defining-processes">Defining processes</h4>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>process</code></td>
<td>Type of actions and defined processes in histories.</td>
</tr>
<tr class="even">
<td><code>process1 + process 2</code></td>
<td>Do either <code>process1</code> or <code>process2</code></td>
</tr>
<tr class="odd">
<td><code>process1 * process 2</code></td>
<td>Sequential composition, do <code>process1</code> followed by <code>process2</code></td>
</tr>
<tr class="even">
<td><code>process1 || process 2</code></td>
<td>Parallel composition, do <code>process1</code> and <code>process2</code> at the same time (interleaved)</td>
</tr>
</tbody>
</table>
<h4 id="history-related-constructs">History-related constructs</h4>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>History hist</code></td>
<td>Declare a History object called <code>hist</code></td>
</tr>
<tr class="even">
<td><code>HPerm(var, p)</code></td>
<td>History-specific permissions where <code>var</code> refers to a variable in the history and <code>p</code> is the amount of permissions (a value between 0 and 1)</td>
</tr>
<tr class="odd">
<td><code>Hist(var, p, val)</code></td>
<td>Similar to <code>PointsTo</code>, it denotes permissions <code>p</code> for variable <code>var</code> (which is in a history). Moreover, variable <code>var</code> points to the value <code>val</code>.</td>
</tr>
<tr class="even">
<td><code>AbstractState(h, boolExpr)</code></td>
<td>This can be used to check that the given boolean expression holds in the history (or future) <code>h</code></td>
</tr>
<tr class="odd">
<td><code>action(h, perm, preState, postState) { ... }</code></td>
<td>The action describes how the state of the history (or future) is changed by the code within <code>{ ... }</code>. The pre- and post-state describe the state of the history (or future) in terms of processes. <code>perm</code> describes the permissions we have on the history (or future) and <code>h</code> is the name of the history (or future)</td>
</tr>
<tr class="even">
<td><code>create h</code></td>
<td>Create a history. Note that the History class should be instantiated beforehand.</td>
</tr>
<tr class="odd">
<td><code>destroy h, val</code></td>
<td>Destroy a history <code>h</code> which has the value <code>val</code></td>
</tr>
<tr class="even">
<td><code>split h, p1, val1, p2, val2</code></td>
<td>Split a history (or future) into two such that the first part has permissions <code>p1</code> and value <code>val1</code> and the second part has permissions <code>p2</code> and value <code>val2</code></td>
</tr>
<tr class="odd">
<td><code>merge h, p1, val1, p2, val2</code></td>
<td>Merge two histories (or futures) such that resulting history <code>h</code> has permissions <code>p1+p2</code> and consists of combination of the actions described in <code>val1</code> and <code>val2</code></td>
</tr>
<tr class="even">
<td><code>modifies vars</code></td>
<td>List of locations that might be modified</td>
</tr>
<tr class="odd">
<td><code>accessible vars</code></td>
<td>List of locations that might be accessed</td>
</tr>
</tbody>
</table>
<h4 id="future-related-constructs">Future-related constructs</h4>
<table>
<thead>
<tr class="header">
<th>Code</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td><code>Future f</code></td>
<td>Declare a Future object called <code>f</code></td>
</tr>
<tr class="even">
<td><code>Future(f, p, val)</code></td>
<td>It denotes that we have permissions <code>p</code> on future <code>f</code> of which the state is <code>val</code></td>
</tr>
<tr class="odd">
<td><code>choose f, p1, pre, post</code></td>
<td>//TODO check this support and definition</td>
</tr>
<tr class="even">
<td><code>create f, val</code></td>
<td>Create a future <code>f</code> with the initial state <code>val</code></td>
</tr>
<tr class="odd">
<td><code>destroy f</code></td>
<td>Destroy the future <code>f</code></td>
</tr>
</tbody>
</table>
<h4 id="other">Other</h4>
<ul>
<li>APerm ??</li>
<li><code>Ls: send p to Lr, d</code> | Release permissions <code>p</code> to the statement labelled <code>Lr</code> with <code>d</code> being the distance of dependence. This statement is labelled <code>Ls</code>.</li>
<li><code>Lr: recv p from Ls, d</code> | Acquire permission <code>p</code> from the statement labelled <code>Ls</code> with the distance of dependence <code>d</code>. This statement is labelled <code>Lr</code>.</li>
<li>Iteration contract | An iteration contract specifies the iteration's resources. The precondition of an iteration contract specifies the resources that a particular iteration needs, and the postcondition specifies the resources that become available after the execution of the iteration.</li>
<li>\pointer(var, length, perm), \pointer_index(...)?</li>
</ul>
<h1 id="developing-for-vercors">Developing for VerCors</h1>
<h2 id="workflow">Workflow</h2>
<p><em>Below you can find a description of what a typical workflow looks like.</em></p>
<ol>
<li>Determine what you want to fix.</li>
<li>If there is an issue for what you want to fix, assign yourself to the issue. This can be done by navigating to the specific page of the issue. Then on the right-hand side there is an "Assignees" header. Beneath this header click on "assign yourself".</li>
<li>Create a fork of VerCors if you have not done this yet. The installation instructions can be found on the main Github page (<a href="https://github.com/utwente-fmt/vercors">https://github.com/utwente-fmt/vercors</a>).</li>
<li>Navigate to the VerCors project on your computer in your terminal.</li>
<li>When you're inside the <code>vercors</code> directory, create a new branch by executing the following command: <code>git branch branch_name</code>. Replace <code>branch_name</code> by the name for your branch. This name can reflect what you will fix. For example if you are going to fix issue 1, then you can name the branch "issue1". Or if you add support for automatic generation of invariants, then you can name the branch "automaticInvariants".</li>
<li>Then switch to the new branch using the command <code>git checkout branch_name</code>.</li>
<li>You can now start working on your problem!</li>
<li>When you are done with your fix, <strong>commit and push</strong> your changed files to the branch. To push to your new branch you can use the following command:<code>git push origin branch_name</code>.</li>
<li>Navigate to the main GitHub page of VerCors (<a href="https://github.com/utwente-fmt/vercors/">https://github.com/utwente-fmt/vercors/</a>).</li>
<li>Create a new pull request (see also: <a href="https://help.github.com/articles/creating-a-pull-request-from-a-fork/">https://help.github.com/articles/creating-a-pull-request-from-a-fork/</a>).</li>
<li>As soon as this pull request has been reviewed by at least one person, and is accepted, then your fix can be merged into the master branch. <em>Congratulations you're done with this PR (Pull Request)!</em></li>
</ol>
<h2 id="review-guidelines">Review guidelines</h2>
<p><em>Below you can find a list of things to take into account when reviewing a pull request. Note that these are also things that you can take into account <strong>before</strong> submitting a pull request.</em></p>
<ul>
<li>Are all newly added pieces of code/methods documented?</li>
<li>Are there no hardcoded Strings/integers/etc.?</li>
<li>Are pieces of code not repeated?</li>
<li>Can all (correct) examples (in the examples directory) still be verified without errors?</li>
</ul>
<p>All questions above should typically be answered with "Yes". If not, then you may want to request changes on the pull request before accepting the changes.</p>
<h1 id="project-structure">Project Structure</h1>
<h3 id="functional-overview">Functional Overview</h3>
<p>VerCors verifies programs by going through three stages:</p>
<ul>
<li><strong>The frontend, or parsing</strong>: in this step a collection of files is read and parsed into COL, the common object language. This is the internal intermediate representation of programs in VerCors.</li>
<li><strong>The rewrite stage</strong>: The COL AST (usually referred to as just the AST) is transformed into a different AST, or it is checked for some property, together called a "pass". Depending on user-supplied options many passes are applied to the AST. They can be divided in several categories:
<ul>
<li><em>Reducing a feature</em>: The subset of COL used is reduced, by encoding the proof obligations of a language construct in some other way. An example is that we reduce <code>for</code> loops to <code>while</code> loops, by placing the initialization before a new while loop, retaining the condition, and appending the update to the end of the loop body.</li>
<li><em>Checking for consistency</em>: in some places it is useful that the type structure of the AST is not as strict as the set of ASTs we want to allow. Almost all checks are done in the type check, a single pass executed many times.</li>
<li><em>Standardization</em>: many passes expect certain properties to be true about the AST (e.g. "expresions have no side effects"), but on the other hand it is useful not to constrain passes too much in what they can generate. Therefor we standardize the AST between passes, to reduce simple language features.</li>
<li><em>Importing builtins</em>: some features are supported by importing a header of sorts into the AST.</li>
<li><em>Optimization</em>: optimizing passes transform the AST (in particular expressions) such that they are faster to prove, or rely less on heuristics of the backend.</li>
</ul></li>
<li><strong>The backend, or verification</strong>: the very much reduced AST is transformed once more, into the language of the selected backend. The backend makes a judgement on the program. This judgement is translated back into useful information about the program.</li>
</ul>
<p>There are also a few cross-cutting concerns:</p>
<ul>
<li><strong>The origin system</strong> tracks how the frontend parse tree is transformed via COL into a backend language. This means that if a message from the backend mentions a part of the AST in the backend language, it can be translated all the way back to its origin in the frontend input.</li>
<li><strong>Logging</strong>: we have a bespoke logging system and outputting via stdout or stderr is forbidden programatically. This makes it possible to set verbosity with the granularity we want (e.g. only verdict, progress information, filter debug information by class)</li>
</ul>
<h3 id="technical-setup">Technical Setup</h3>
<p>The VerCors project sources are managed on GitHub, at <a href="https://github.com/utwente-fmt/vercors">https://github.com/utwente-fmt/vercors</a>. The unstable development branch is <code>dev</code>, from where we branch to develop new features and bugfixes. Those with access may push feature branches to the main repository, or create a fork if they prefer. A pull request is then made and reviewed by someone appropriate.</p>
<p>Pushed commits as well as pull requests are checked via continuous integration, currently Travis + Sonarcloud. Travis builds Vercors and runs the test suite. The (committed) code is linted by Sonarcloud. Once the checks pas and a code review is completed, the PR is merged in dev. In dev is also where we hope to notice bad interactions between new features.</p>
<p>We aim to make a new VerCors release once per month, which is done via a tagged merge commit to the <code>master</code> branch, followed by a GitHub release.</p>
<p>VerCors is written in java and scala. Code is accepted in either language, but all things being equal scala is preferred. The build structure is defined in the scala build tool. We use the sbt-native-packager plugin to package the tool as a .deb and a .tar.gz (compiled). The sbt-buildinfo plugin supplies the tool with run-time access to versioning and build time information.</p>
<h3 id="project-structure-1">Project Structure</h3>
<h4 id="section"><code>/</code></h4>
<ul>
<li><code>build.sbt</code> is the root build definition. It configures global plugin options, wires sub-project dependencies, configures run-time build information, denotes external dependencies, configures metadata about the project and configures compiler options.</li>
<li><code>.travis.yml</code> sets up caching and parallelizes the build. The actual build steps are in <code>.travis/build.sh</code>.</li>
</ul>
<h4 id="travis"><code>/.travis</code></h4>
<ul>
<li><code>travis_fold.sh</code>: Wrapper script that causes travis to make a foldable section of text.</li>
<li><code>travis_tar.sh</code>: Replacement for the system <code>tar</code> that packs our cache in a format that SBT ends up being happy with. Should be default by now <a href="https://travis-ci.community/t/change-tar-format-to-posix/5467">https://travis-ci.community/t/change-tar-format-to-posix/5467</a></li>
</ul>
<h4 id="bin"><code>/bin</code></h4>
<p>Convenience run scripts that targets the last-compiled classes in the vercors directory. This is the least stable method of running Vercors. If bleeding edge features are not needed, use the release version. If possible, run Vercors imported in an IDE instead. Failing that, the run scripts can be useful.</p>
<ul>
<li><code>run-class.sh</code> and <code>.classpath</code>: run-class obtains the run-time class path from SBT and caches it in .classpath. A supplied main class + arguments is run from this class path. Other scripts in the directory only select a main class and refer to run-class.</li>
</ul>
<h4 id="examples"><code>/examples</code></h4>
<p>This directory serves the dual purpose of being an archive of past case studies and competition results, as well as the test suite of VerCors. Files in this directory have a header denoting how they are used in the test suite. Names denoted by "case" or "cases" are collected, where overlapping names are joined together as one test case. Files may occur in more than one "case." "tools" denotes the backend used (silicon by default), "verdict" the expected final result (Pass by default).</p>
<ul>
<li><code>private</code>: this can be created as a subdirectory and is ignored by git. Contributing examples (however small) that reproduce issues are however appreciated.</li>
</ul>
<h4 id="parsers"><code>/parsers</code></h4>
<p>A sub-project of Vercors that implements the parsing infrastructure of Vercors.</p>
<ul>
<li><code>build.sbt</code>: runs ANTLR 4, the parser generator, prior to compilation if necessary. Grammar source sets are encoded explicitly in the build file.</li>
<li><code>src/main/antlr/*.g4</code>: root grammars from which parsers are generated. These only glue a language grammar to the specification grammar, though the language grammar itself is also modified somewhat.</li>
<li><code>lib/antlr4/*.g4</code>: non-root grammars, defining languages (such as C and Java), embedded languages (such as OpenMP) and specifications.</li>
</ul>
<h4 id="project"><code>/project</code></h4>
<p>An artifact of how SBT works. SBT projects are layered, not in the sense that we have sub-project dependencies, but that you can define meta-projects. This directory defines a meta-project, which means it is available in compiled form in the upper build definition. We just use it to add some plugins to the compiler.</p>
<h4 id="splitverify"><code>/SplitVerify</code></h4>
<p>This tool is not part of VerCors itself, but interacts with VerCors to cache the verification result of <em>parts</em> of a test case. That means that changing part of a test case only requires re-verifying the changed part. Refer to the readme there for further information.</p>
<h4 id="util"><code>/util</code></h4>
<ul>
<li><code>VercorsPVL.tmbundle</code>: textmate bundle, defining PVL as a grammar, enabling syntax highlighting for PVL in some editors.</li>
</ul>
<h4 id="src"><code>/src</code></h4>
<p>The sources associated with the top-level SBT project, i.e. the main part of VerCors.</p>
<h4 id="srcuniversalres"><code>/src/universal/res</code></h4>
<p>Here live the resources of VerCors. Normally they would be in a directory <code>res</code> or so in the root, but we have a custom solution to ensure that they remain regular files, even when packed into a jar by the build tool. Universal refers to the native-packager also being called the "Universal" plugin. "res" is the name of the directory in which the resources end up in .tar.gz deployment.</p>
<ul>
<li><code>config</code>: Include files ("headers") that are put in the AST at some intermediate stage.
<ul>
<li><code>config/prelude(_C)?.sil</code>: here the standard axiomatic data types that we need are defined</li>
<li>*.jspec: simplification rules that we assume to be true, applied at various stages.</li>
</ul></li>
<li><code>deps</code>: Software dependencies that we do not refer to in the project structure, but rather by including the binary.</li>
<li><code>include</code>: Include files ("headers") that are available to front-end files, though currently only C.</li>
<li><code>selftest</code>: Ancient test files that are still accessible via the test suite. Unclear if they still work.</li>
</ul>
<h4 id="srcmainscala"><code>/src/main/scala</code></h4>
<p>Please don't use this directory anymore. Scala sources also compile fine when included in the java directory, so this "mirror" of the package structure only serves to confuse where classes are defined. Only a few rewrite passes are defined here.</p>
<h4 id="srcmainjava"><code>/src/main/java</code></h4>
<ul>
<li><code>col/lang</code>: I believe this is an implementation of some verification constructs, to facilitate compiling PVL to Java.</li>
<li><code>puptol</code>, <code>rise4fun</code>: external tool support, now obsolete.</li>
</ul>
<h4 id="srcmainjavavct"><code>/src/main/java/vct</code></h4>
<ul>
<li><code>antlr/parser</code>: Takes ANTLR parse trees and converts them to COL. The classes ending in <code>Parser</code> guide the conversion process. Classes ending in <code>ToCOL</code> do the actual immediate translation. Finally there are a few rewrite passes that solve language-specific problems.</li>
<li><code>boogie</code>: Old (?) prototypes for supporting boogie, dafny and chalice.</li>
<li><code>col/annotate</code>: A single feature rewrite pass, unclear why it's in a different package.</li>
<li><code>col/ast</code>: the AST of COL, divided into expressions (<code>expr</code>), declarations (<code>stmt/decl</code>), statements (<code>stmt/{composite,terminal}</code>) and types (<code>type</code>). Also defines some abstract intermediate node types (<code>generic</code>) and utility classes for traversing the tree in various ways (<code>util</code>).</li>
<li><code>col/print</code>: logic to translate COL into text again for different frontends. Only Java is well-supported, as it's the default diagnostic output.</li>
<li><code>col/rewrite</code>: this is where all rewrite passes are defined. They are under-documented, but the <code>Main</code> class offers a brief explanation for each of them.</li>
<li><code>col/syntax</code>: abstract representation of the syntax of various frontends.</li>
<li><code>demo</code>: unclear why this is in the source tree.</li>
<li><code>error</code>: probably an attempt at better abstracting errors from the backend</li>
<li><code>learn</code>: experiment that counts the different types of nodes in the AST, to attempt to correlate verification time with the presence of particular nodes.</li>
<li><code>logging</code>: barely used; abstracts messages and errors for a particular pass</li>
<li><code>main</code>: entry points for VerCors, as well as some poorly-sorted classes.
<ul>
<li><code>ApiGen</code>: echoes back a java program immediately after parsing something; purpose unclear.</li>
<li><code>BoogieFOL</code>: another prototype to interface with boogie, a deprecated backend (?)</li>
<li><code>Brain</code>, <code>SMTinter</code>, <code>SMTresult</code>, <code>Translator</code>: attempt by student (?) to interface with z3</li>
<li><code>Main</code>: the true entry point of Vercors.</li>
</ul></li>
<li><code>silver</code>: maps COL to Silver, the language used by the viper project.</li>
<li><code>test</code>: the testing framework of Vercors. Collects cases from the examples directory and tests each in an isolated process.</li>
<li><code>util/Configuration.java</code>: contains most command-line options, as well as the logic to determine the location of external tools (z3, silicon, etc.)</li>
</ul>
<h4 id="viper"><code>/viper</code></h4>
<p>More logic to interface with the viper backend(s), unclear where exactly the cut is with regards to <code>/src/main/java/vct/silver</code>.</p>
<h4 id="viperhre"><code>/viper/hre</code></h4>
<p>The general utilities project ("Hybrid Runtime Environment"), located here so <code>/viper</code> may depend on it. From here under <code>src/main/java</code>:</p>
<ul>
<li><code>hre/ast</code>: definitions for the different types of <code>Origin</code>.</li>
<li><code>hre/config</code>: defines different types of command line options (integer, choice, collect...)</li>
<li><code>hre/debug/HeapDump</code>: dumps a class and its fields recursively using reflection. Largely replaced by <code>DebugNode</code></li>
<li><code>hre/io</code>: inter-process communication.</li>
<li><code>hre/lang/System</code>: defines the logging system. Currently output to stdout and stderr directly is forbidden programatically, so that you're forced to use the logging framework.</li>
</ul>
<h1 id="ide-import">IDE Import</h1>
<p>This document gives instructions on how to configure a development environment for VerCors, using either Eclipse or Intellij IDEA. Below the instructions there is a list of encountered errors, you might want to check there if you encounter one. The setup for IntelliJ IDEA is considerably easier, so we recommend using that.</p>
<h3 id="configuring-vercors-for-eclipse">Configuring VerCors for Eclipse</h3>
<p>First make sure that Eclipse is installed. The installation instructions described below have been tested with Ubuntu 18.04 and Eclipse version 4.11.0 (March 2019) from snap, not apt. The version from the eclipse website should be equivalent. Also make sure that VerCors has been installed according to the instructions given on the <a href="https://github.com/utwente-fmt/vercors">main Git page</a>.</p>
<p>Install the Scala IDE plugin for Eclipse. Download- and installation instructions can be found <a href="http://scala-ide.org/download/current.html">here</a>. After providing the installation/update site in Eclipse, make sure that <em>all</em> the suggested options are selected for installation. After installation has completed, navigate to Help &gt; Install new software &gt; Manage, and go to Scala &gt; Compiler. We have to give scalac more memory to compile VerCors than is available by default, so enter "-J-Xmx2048m" under "Additional command line options" to increase the limit to 2048MB.</p>
<p>Install the eclipse plugin for sbt, instructions for which can be found <a href="https://github.com/sbt/sbteclipse">here</a>. It is recommended that you install it in the global file, and not in the vercors file, as we do not want to add eclipse-specific configuration in the repository. Create a global settings file for SBT (e.g. in ~/.sbt/1.0/global.sbt) and add the setting <code>EclipseKeys.skipParents in ThisBuild := false</code></p>
<h4 id="configuring-the-vercors-modules">Configuring the VerCors modules</h4>
<ol>
<li>Navigate to the vercors subdirectory using a terminal, and run <code>sbt eclipse</code>. This will generate all the eclipse projects necessary to build vercors within eclipse.</li>
<li>In eclipse, go to File &gt; Import and select General &gt; Existing Projects into Workspace. Select the vercors subdirectory, and tick Search for nested projects. If all went well, it should list all the vercors subprojects (vercors, hre, parsers, viper, silicon, etc.). Click finish.</li>
<li>Eclipse will automatically build vercors; wait for it to finish. If eclipse fails to build the projects in this stage, unfortunately the projects are left in a broken state. You should remove all the projects or clear the workspace, and try again.</li>
</ol>
<h4 id="running-vercors-from-eclipse">Running VerCors from Eclipse</h4>
<p>To run VerCors from Eclipse, and thereby allow debugging within Eclipse, a <em>Run Configuration</em> needs to be created and used. To create a run configuration, do the following. In the menu, navigate to "<em>Run &gt; Run Configurations...</em>". Select "<em>Java Application</em>" in the left menu and press the "New" button/icon in the toolbar. From here you can assign a name to the new configuration, for example "VerCors". Under "<em>Project</em>" select the "Vercors" project. Under "<em>Main class</em>" select or type <code>vct.main.Main</code>. Under the "<em>Arguments</em>" tab, type <code>${string_prompt}</code> in the "<em>Program arguments</em>" field, so that every time VerCors is run with this new configuration, a pop-up window will be given in which arguments to VerCors can be specified. Moreover, in the "<em>VM arguments</em>" field, type <code>-Xss16m</code>, which increases the stack size to 16MB for VerCors runs (the default stack size may not be enough). Set the working directory to the root directory of the git repository.</p>
<p>After performing these steps, you should be able to run VerCors from Eclipse via the "<em>Run</em>" option (do not forget selecting the new run configuration). The output of VerCors is then printed in Eclipse console view. To validate the configuration, try for example "--silicon examples/manual/fibonacci.pvl". If you get an error like <code>NoClassDefFoundError: scala/Product</code>, perform the workaround listed under "I got an error!" below.</p>
<h3 id="configuring-vercors-for-intellij-idea">Configuring VerCors for IntelliJ IDEA</h3>
<p>The steps below were testing with Ubuntu 18.04 and Intellij IDEA community edition, version 2019.1.1 retrieved via snap. Also make sure that VerCors has been installed according to the instructions given on the <a href="https://github.com/utwente-fmt/vercors">main Git page</a>.</p>
<p>When first installed, go to Configure &gt; Plugins, or when already installed go to File &gt; Settings &gt; Plugins. Install the Scala plugin, and restart the IDE as suggested.</p>
<h4 id="configuring-the-project">Configuring the project</h4>
<ol>
<li>Go to Open when first installed, File &gt; Open otherwise, and open vercors/build.sbt. Open it as a project as suggested. In the dialog "Import Project from sbt", you should tick "Use sbt shell: for imports" and "for builds"</li>
<li>Wait for the running process to complete, which configures the whole project.</li>
<li>If IntelliJ did not present you with the option "Use sbt shell [] for imports [] for build"; open File &gt; Settings, and tick the two options in Build, Execution, Deployment &gt; Build Tools &gt; sbt.</li>
</ol>
<h4 id="running-vercors-from-intellij-idea">Running VerCors from IntelliJ IDEA</h4>
<p>Go to Run &gt; Edit configurations, and add a Java application with the following settings:</p>
<ul>
<li>Main class: <code>vct.main.Main</code></li>
<li>VM options: <code>-Xss16m</code> (increases the stack size to 16MB)</li>
<li>Program arguments: <code>$Prompt$</code> (asks for parameters every time)</li>
<li>Working directory: the root of the git repository</li>
<li>Use classpath of module: <code>Vercors</code></li>
</ul>
<p>You should now be able to run and debug VerCors from within IntellJ IDEA! The first time the run configuration is used the project will be built in full, so that will take some time.</p>
<h3 id="i-encountered-an-error">I encountered an error!</h3>
<p>Here is a collection of errors we encountered, with a solution. Is your problem not here? Please file an issue!</p>
<ul>
<li><em>While eclipse is building the workspace, I get a StackOverflowException.</em> The scala compiler has insufficient memory to compile the project, try increasing the limit at Help &gt; Install New Software &gt; Manage, then Scala &gt; Compiler. Try adding -J-X____m at "Additional command line parameters", substituting ___ with the amount of memory (in MB) you want to try.</li>
<li><em>Silicon is not detected as a project when running <code>sbt eclipse</code>.</em> By default, parent projects (SBT projects that aggregate other projects) are not included by this tool. Remember to add <code>EclipseKeys.skipParents in ThisBuild := false</code> to the global SBT settings file.</li>
<li><em>Only VerCors is detected as a project by eclipse.</em> Remember to tick "Search for nested projects".</li>
<li><em>The run configuration only outputs NoClassDefFoundError: scala/Product.</em> For whatever reason, the scala runtime environment is not included in the run configuration. You should add manual dependencies in the run configuration, at Dependencies &gt; Classpath Entries &gt; Add External JARs. Add <code>scala-library.jar</code> and <code>scala-reflect.jar</code>, which should be present in the sbt configuration directory, <code>~/.sbt/boot/scala-x.x.x/</code> or similar. It seems to me that these libraries should be included from the project classpath, so please let me know if I'm misunderstanding!</li>
<li><em>While IntelliJ is building, it encounters import errors in package <code>vct.antlr4.generated</code></em>. When creating the project from <code>vercors/build.sbt</code>, do not forget to tick "Use sbt shell: for builds", do not tick imports, and untick "allow overriding sbt version". I only encountered this on initial setup of the project and sometimes "accidentally" fixed it, so please let me know if you did set these options, but got this result anyway.</li>
</ul>
