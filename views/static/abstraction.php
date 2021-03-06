<?php
    $this->title = 'Overview';
?>
<section>
    <header class="major">
        <h2><strong style="color:#3ac984"> Formal Program Verification with Abstractions</strong></h2>
        <span class="byline">Model-based Verification</span>
    </header>
    <p style="text-align:justify"> At layer 3, VerCors combines data abstraction with process algebra and allows
        you to reason about abstract model by defining corresponding actions & processes with contracts. In the
        top layer the notion of history is added which allows us to verify functional properties in addition to
        data race freedom. A history captures abstractly the updates to data. Updates to histories are traced
        locally, and when threads synchronize, their local histories are merged into a global history, capturing
        the possible interleavings between the local updates. Note that functional properties could be accounted
        for using resource invariants and thread-locality only. However, this would give rise to large and
        complex invariants, because the invariant has to take into account how everything fits </p>
    <IMG SRC="/images/PA.png" width="40%" height="40%" ALIGN="left"/>


    <p style="text-align:justify">In the context of heterogeneous threading, verification of functional
        properties is a major challenge, requiring suitable abstractions. We have developed a technique to
        capture the behaviour of a shared memory concurrent program by means of a process algebra term. All
        accesses to the rel- evant shared memory locations are abstracted by actions. The process algebra term
        specifies what are the legal sequences of actions that are allowed to occur, and the program logic is
        used to verify that the process algebra term is indeed a correct abstraction of the program. Functional
        properties about the program can then be verified using algorithmic reasoning on the process algebra
        term.
    </p>

    <h3>References</h3>
    <li><a href="https://link.springer.com/chapter/10.1007/978-3-319-72308-2_12">An Abstraction Technique for
            Describing Concurrent Program Behaviour</a></li>
    <li><a href="https://link.springer.com/chapter/10.1007/978-3-319-66845-1_7">The VerCors Tool Set:
            Verification of Parallel and Concurrent Software</a></li>
</section>