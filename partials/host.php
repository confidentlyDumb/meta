
<main>
    <div class="player_actions">
        <div class="p_head">
            <?php $this_player = get_sf_id($_SESSION['host']); ?>
            <h3>Код доступа для игрока
            <?php echo isset($this_player) ? ' <span>' . $this_player . '</span>'
            : ' <span>не существует. Кликните по кнопке на нижней панели, чтобы создать новый.</span>'; ?>
            </h3>
        </div>
        <div class="p_body">
            <?php build_p_body(); ?>
        </div>
    </div>
    <div class="p_img">
        <img src="/assets/img/card_black.jpg" alt="Current card" class="p_test">
        <div>
            Описание карты будет отображено здесь.
        </div>
    </div>
</main>
<nav>
    <a id="start_abort"><img src="/assets/img/logo.png" alt=""></a>
</nav>
