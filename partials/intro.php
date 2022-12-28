<?php vrf_client_ip($user, 0) == true ? header("Location: /") : vrf_client_ip($user, 1); ?>
<main class="i_wrap">
    <video autoplay muted controls preload="auto">
        <source src="/assets/vid/intro.mp4">
    </video>
    <a id="skip"><img src="/assets/img/skip.png" alt=""></a>
</main>