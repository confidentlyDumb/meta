<div class="card-wrapper">
    <img class="current_card container" src="" alt="current card">
    <div class="current_desc"></div>
</div>

<?php $started = isset($_SESSION['start']) && $_SESSION['start'];
if (!$started) { ?>

<div class="i_text show">
    <h1>
        Трансформационная игра
    </h1>
        <img src="assets/img/logo_finl.png" alt="">
    <h1>
        Реализуй свою мечту
    </h1>
</div>

<div class="dice">
    <ol class="die-list even-roll" data-roll="1">
        <?php dice_build(); ?>
    </ol>
</div>

<?php } ?>

<main class="cards">
    <?php deck_build('meta'); ?>
    <?php deck_build('astro', $started); ?>
</main>
<nav>
    <a id="meta"><img src="/assets/img/logo_bbg.png" alt=""></a>
    <a id="dice"><img src="/assets/img/dice.png" alt=""></a>
    <a id="astro"><img src="/assets/img/star.png" alt=""></a>
</nav>