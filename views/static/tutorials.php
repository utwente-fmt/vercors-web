<?php
$this->title = 'Tutorials';
?>

<header class="major">
    <h2><strong>VerCors Tutorials</strong></h2>
</header>

<section>
    <h2>
        <a href='https://github.com/wytseoortwijn/vercors/wiki/A-Brief-Introduction-to-VerCors'>Introduction
            to VerCors
        </a>
    </h2>
    <p>
    VerCors is a toolset that allows to reason about data-race freedom, memory safety and functional
    correctness of high-level parallel and concurrent languages, like Java, OpenCL and OpenMP. This document
    contains a brief introduction to static verification with the VerCors toolset, in the form of a
    tutorial.
    </p>

    <h2>
        <a href='https://github.com/utwente-fmt/vercors/wiki'>Eclipse and
            IntelliJ Vercors Setup
        </a>
    </h2>
    <p>
    This document gives instructions on how to configure a development environment for VerCors, using either
    Eclipse or Intellij IDEA. You can also find a list of common instalation errors. The setup for IntelliJ
    IDEA is considerably easier, so we recommend using that.
    </p>

    <h2>
        <a href='https://github.com/utwente-fmt/vercors/wiki/Axiomatic-Data-Types'>Axiomatic Data
            Types
        </a>
    </h2>
    <p>
    This page discusses the axiomatic data types (ADTs) that are supported by VerCors. Some of these ADTs
    like sequences and sets are natively supported by the Viper toolset, the main back-end of VerCors. ADTs
    that are not natively supported, like matrices, vectors, and option types, are specified as domains in
    the config/prelude.sil file (specified in the Silver language). During the translation steps in VerCors,
    the SilverClassReduction class includes all domains that are needed to verify the input program.
    </p>

    <h2>
        <a href='https://github.com/utwente-fmt/vercors/wiki/Prototypal-Verification-Language'>The PVL
            language
        </a>
    </h2>
    <p>
    This page discusses the language features of PVL, the Prototypal Verification Language of VerCors. In
    particular, it elaborates on design choices and gives implementation details.
    </p>

    <h2>
        <a href='https://github.com/utwente-fmt/vercors/wiki/PVL-Syntax'>PVL
            Syntax
        </a>
    </h2>
    <p>
    On this page you can find a description of the syntax of PVL, Prototypal Verification Language; one of
    the languages for which VerCors supports verification.
    </p>

    <h2>
        <a href='https://github.com/utwente-fmt/vercors/wiki/What-does-this-VerCors-error-message-mean%3F'>What
            does this VerCors error message mean?
        </a>
    </h2>
    <p>
    In this tutorial you will learn what the different error messages in VerCors mean.
    </p>

    <h2>
        <a href='https://github.com/utwente-fmt/vercors/wiki/Developing-for-VerCors'>Developing for
            VerCors
        </a>
    </h2>
    <p>
    In this tutorial you can find guidelines and a workflow description for contributing to the VerCors
    developement.
    </p>
</section>
