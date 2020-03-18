<?php

use app\assets\AppAsset;

$this->title = 'Installation Guide';

?>

<header class="major" style="padding: 0">
    <h2>Installation Instructions</h2>
</header>

<div class="installation-tabs">
    <input type="radio" name="os" id="linux" checked><label for="linux">Debian Linux</label>
    <input type="radio" name="os" id="mac"><label for="mac">Unix/MacOS</label>
    <input type="radio" name="os" id="windows"><label for="windows">Windows</label>
    <input type="radio" name="type" id="install" checked><label for="install" style="float: right">Installation</label>
    <input type="radio" name="type" id="build"><label for="build" style="float: right">Building</label>
    <div></div>

    <div class="installation-tab" id="install-linux">
        <h3>Installation Instructions for Debian Linux</h3>
        <p>VerCors needs a java runtime environment of version 8 or greater. For C support, VerCors also needs <code>clang</code> to process header files. You can install them via <code>apt</code>:</p>
        <pre>apt-get install openjdk-8-jre clang</pre>
        <p>Next, obtain a <code>.deb</code> release of VerCors <a href="https://github.com/utwente-fmt/vercors/releases/latest">here</a>, and install it using:</p>
        <pre>dpkg -i Vercors_x.y.z_all.deb</pre>
        <p>VerCors is now available on the path as <code>vercors</code>.</p>
    </div>

    <div class="installation-tab" id="install-mac">
        <h3>Installation Instructions for Other Unix/MacOS</h3>
        <p>VerCors needs a java runtime environment of version 8 or greater. The shell script that starts VerCors obtains java from the <code>JAVA_HOME</code> environment variable, so make sure that is set. The easiest way to obtain a JRE on MacOS is through homebrew:</p>
        <pre>brew cask install java</pre>
        <p>For C support VerCors needs <code>clang</code> to process header files. On MacOS this should be already installed if you have installed XCode. Other unix can obtain a copy of <code>clang</code> <a href="https://releases.llvm.org/download.html">here</a>.</p>
        <p>Finally, obtain a <code>.zip</code> copy of VerCors <a href="https://github.com/utwente-fmt/vercors/releases/latest">here</a> and unpack it. The runnable executable is <code>bin/vercors</code>.</p>
    </div>

    <div class="installation-tab" id="install-windows">
        <h3>Installation Instructions for Windows</h3>
        <p>VerCors needs a java runtime environment of version 8 or greater. If you do not have one installed, you can obtain OpenJDK <a href="https://jdk.java.net/">here</a>. The batch file that starts VerCors obtains java from the <code>JAVA_HOME</code> environment variable, so make sure that is set.</p>
        <p>For C support VerCors needs <code>clang</code> to process header files. You can obtain a copy of <code>clang</code> <a href="https://releases.llvm.org/download.html">here</a>. Make sure that the <code>clang</code> executable is included in the <code>PATH</code> environment variable.</p>
        <p>Finally, obtain a <code>.zip</code> copy of VerCors <a href="https://github.com/utwente-fmt/vercors/releases/latest">here</a> and unpack it. You can now run <code>bin/vercors.bat</code> from a command prompt.</p>
    </div>

    <div class="installation-tab" id="build-linux">
        <h3>Build Instructions for Debian Linux</h3>
        <p>The prerequisites to build VerCors are:</p>
        <ul>
            <li>A Java Development Kit, version 8 or later</li>
            <li>Git (to clone VerCors)</li>
            <li>Mercurial (to clone source dependencies of VerCors)</li>
            <li>SBT, the build tool of Scala</li>
        </ul>
        <p>For SBT, refer to the <a href="https://www.scala-sbt.org/download.html">download page</a> for instructions. The other dependencies can be installed via <code>apt</code>:</p>
        <pre>apt-get install openjdk-8-jdk git mercurial</pre>
        <p>Next, clone the VerCors repository:</p>
        <pre>git clone https://github.com/utwente-fmt/vercors.git</pre>
        <p>Change to the <code>vercors</code> directory and compile with SBT:</p>
        <pre>sbt compile</pre>
        <p>The above step may take a while, as it first fetches the correct SBT version, then the dependencies of VerCors, and then builds VerCors. Lastly, you can check whether the compilation succeeded by running a small part of the test suite:</p>
        <pre>./bin/vct --test=examples/manual --tool=silicon --lang=pvl,java --progress</pre>
        <p>This should report <code>All ? tests passed</code>. If so, you can now start hacking on VerCors! If you prefer, you can also import VerCors into either eclipse or Intellij IDEA. Instructions for that can be found <a href="https://github.com/utwente-fmt/vercors/wiki">here</a></p>
    </div>

    <div class="installation-tab" id="build-mac">
        <h3>Build Instructions for Other Unix/MacOS</h3>
        <p>The prerequisites to build VerCors are:</p>
        <ul>
            <li>A Java Development Kit, version 8 or later</li>
            <li>Git (to clone VerCors)</li>
            <li>Mercurial (to clone source dependencies of VerCors)</li>
            <li>SBT, the build tool of Scala</li>
        </ul>
        <p>For SBT, refer to the <a href="https://www.scala-sbt.org/download.html">download page</a> for instructions. The other dependencies are left as an exercise to the reader.</p>
        <p>Next, clone the VerCors repository:</p>
        <pre>git clone https://github.com/utwente-fmt/vercors.git</pre>
        <p>Change to the <code>vercors</code> directory and compile with SBT:</p>
        <pre>sbt compile</pre>
        <p>The above step may take a while, as it first fetches the correct SBT version, then the dependencies of VerCors, and then builds VerCors. Lastly, you can check whether the compilation succeeded by running a small part of the test suite:</p>
        <pre>./bin/vct --test=examples/manual --tool=silicon --lang=pvl,java --progress</pre>
        <p>This should report <code>All ? tests passed</code>. If so, you can now start hacking on VerCors! If you prefer, you can also import VerCors into either eclipse or Intellij IDEA. Instructions for that can be found <a href="https://github.com/utwente-fmt/vercors/wiki">here</a></p>
    </div>

    <div class="installation-tab" id="build-windows">
        <h3>Build Instructions for Windows</h3>
        <p>The prerequisites to build VerCors are:</p>
        <ul>
            <li>A Java Development Kit, version 8 or later</li>
            <li>Git (to clone VerCors, see <a href="https://git-scm.com/downloads">here</a>)</li>
            <li>Mercurial (to clone source dependencies of VerCors, see <a href="https://www.mercurial-scm.org/downloads">here</a>)</li>
            <li>SBT, the build tool of Scala (see <a href="https://www.scala-sbt.org/download.html">here</a>)</li>
        </ul>
        <p>You can obtain OpenJDK <a href="https://jdk.java.net/">here</a>. Make sure that once unpacked, the <code>PATH</code> environment variable has the directory that contains <code>java.exe</code>.</p>
        <p>Next, open a command prompt and clone the VerCors repository:</p>
        <pre>git clone https://github.com/utwente-fmt/vercors.git</pre>
        <p>Change to the <code>vercors</code> directory and compile with SBT:</p>
        <pre>sbt compile</pre>
        <p>The above step may take a while, as it first fetches the correct SBT version, then the dependencies of VerCors, and then builds VerCors. Lastly, you can check whether the compilation succeeded by running a small part of the test suite:</p>
        <pre>./bin/vct --test=examples/manual --tool=silicon --lang=pvl,java --progress</pre>
        <p>This should report <code>All ? tests passed</code>. If so, you can now start hacking on VerCors! If you prefer, you can also import VerCors into either eclipse or Intellij IDEA. Instructions for that can be found <a href="https://github.com/utwente-fmt/vercors/wiki">here</a></p>
    </div>
</div>

<!--
<div class="wrapper style1">
    <div class="installation">
        <h3 class="section__title" id="install">Installation Instructions</h3>
        <div class="tab__container">
            <ul class="tab__menu">
                <li class="tab active" data-tab="mac">MacOS</li>
                <li class="tab" data-tab="linux">Linux</li>
                <li class="tab" data-tab="win">Windows</li>
            </ul>
            <pre class="nohighlight code">
            <code class="tab__pane active mac"> <h2 class="section__title">Requirements:</h2>
	<ul>
<li> <code> Be sure to have installed Homebrew </code> </li>
<li><code> $ brew tap homebrew/cask-versions </code> </li>
<li> <code>$ brew cask install java8 </code> </li>
<li> <code>$ brew install ant </code></li>
</ul>

<h2 class="section__title">Building:</h2>

<p>For a basic build of VerCors the following steps should be taken:</p><ol start=''><li>Clone the VerCors repository using <code>$ git clone https://github.com/utwente-fmt/vercors.git</code> and move into the cloned directory, <code>$ cd vercors</code> </li>
	<li>Create symbolic links to link the Viper modules, as described on the <a
                href="https://github.com/utwente-fmt/vercors/tree/master/vercors/viper">vercors/viper page</a>. Users with a Unix system can also use the travis_build.sh script to create symbolic links and install VerCors by running <code>$ sh travis_build.sh</code> from the project's root directory.</li>
	<li>Build VerCors with Ant by running <code>$ ant clean</code> followed by <code>$ ant</code> </li>
	<li>Test whether the build was successful by running <code>$ ./unix/bin/vct --test=examples/manual --tool=silicon --lang=pvl,java</code></li>
</ol>
<p>The last command tests the VerCors installation by verifying a large collection of examples (from the <em>./examples</em> directory). This command should eventually report that <code>$ all ? tests passed </code></p>
</code>

<code class="tab__pane win">
The VerCors toolset can be installed on Windows (via Cygwin). When using Windows make sure that the environment variable <em>JAVA_HOME</em> is configured. <h3
            class="section__title">Requirements:</h3></p>
    <ul><li>Java Development Kit (JDK), version 8 (the current version of VerCors does <em>not</em> work with Java 9!)</li><li>Git (on Windows you need Git Bash, see <a
                    href='https://git-scm.com/downloads' target='_blank' class='url'>https://git-scm.com/downloads</a>)</li><li>Apache Ant, version 1.9.9 (see <a
                    href='http://ant.apache.org' target='_blank' class='url'>http://ant.apache.org</a> for instructions)</li><li>Apache Commons (from <a
                    href='https://commons.apache.org/proper/commons-lang/' target='_blank' class='url'>https://commons.apache.org/proper/commons-lang/</a>)</li><li>Scala SBT (see <a
                    href='http://www.scala-sbt.org' target='_blank' class='url'>http://www.scala-sbt.org</a> for instructions)</li></ul>
</code>

 <code class="tab__pane linux">
 <p>For a basic build of VerCors the following steps should be taken:</p><ol start=''>
	 <li>Clone the VerCors repository using <code>$ git clone https://github.com/utwente-fmt/vercors.git</code> and move into the cloned directory, <code>$ cd vercors</code> </li>
	 <li>Create symbolic links to the viper modules by running <code>$ sh travis_build.sh</code> from the project's root directory.</li>
	 <li>Build VerCors with Ant by running <code>$ ant clean</code> followed by <code>$ ant</code> </li>
	 <li>Test whether the build was successful by running <code>$ ./unix/bin/vct --test=examples/manual --tool=silicon --lang=pvl,java</code></li>
 </ol>
 <p>The last command tests the VerCors installation by verifying a large collection of examples (from the <em>./examples</em> directory). This command should eventually report that <code>$ all ? tests passed </code></p>
								</code>
            
          </pre>
        </div>
    </div>
    <div class="keybinding">
        <ul class="keybinding__detail">
            <h2 class="keybinding__title">Usage Instructions</h2>
            <p style="text-align:justify"> VerCors verifies programs that are annotated with JML-style specifications
                (the underlying theory uses separation logic with permission accounting). Details on the specification
                language can be found on the VerCors <a href='https://github.com/utwente-fmt/vercors/wiki'>Wiki
                    pages</a>. Furthermore, a large collection of example programs can be found (and verified) in the
                <code>./examples</code> directory.The VerCors toolset can be used from the main directory by running
                <span class="keybinding__label">./unix/bin/vct --silver=silicon &lt;filepath&gt;</span> with <span
                        class="keybinding__label">&lt;filepath&gt;</span> the path of the <em>(Java, C, or PVL)</em>
                file to verify.</p>
        </ul>
        <ul class="keybinding__detail">
            <h3 class="keybinding__title">Prototypal Verification Language <br> Syntax Highlighting</h3>
            <p style="text-align:justify">VerCors provides syntax highlighting support for PVL in TextMate 2 (MacOS X)
                and Sublime Text (Linux and Windows) as a TextMate Bundle. The bundle is located at <span
                        class="keybinding__label">/util/VercorsPVL.tmbundle</span>. On MacOS X for TextMate 2 one can
                simply double click the <span class="keybinding__label">.tmbundle</span> file to install it. Sublime Tex
                requires you to copy the bundle content to some directory:
            <ul start=''>
                <li>In Sublime Text, click on the <span
                            class="keybinding__label">Preferences &gt; Browse Packages…</span> menu.
                </li>
                <li>Create a new directory and name it <span class="keybinding__label"> VercorsPVL</span>.</li>
                <li>Move the contents of <span class="keybinding__label">VercorsPVL.tmbundle</span> (that is, the <span
                            class="keybinding__label"> ./Syntaxes</span> directory) into the directory you just created.
                </li>
                <li>Restart Sublime Text.</li>
            </ul>
            <li> For more information about PVL syntax
                <button onclick="window.location.href = 'https://github.com/utwente-fmt/vercors/wiki/PVL-Syntax';">
                    click here
                </button>
            </li>
            </p>
    </div>

    <div class="callout">
        <p>Read our documentation for detailed features and customization</p>
        <a href="{{"/Tutorials.html"| prepend: site.baseurl}}" target=_blank" class="button--primary">Documentation</a>
    </div>
</div>
-->