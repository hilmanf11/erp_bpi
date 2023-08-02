<body>
    <div class="greeting" style="height: 100px; background-position: center; background-repeat: no-repeat; background-size: cover; background-image: url(<?= $background ?>) !important;">
        <div style="float: left;">
            <p style="font-size: 25px !important; margin:0;"><?= $day ?>, <b style="font-size: 25px !important;"><?= $session_name ?></b>!</p>
            <span>It's <?= date("D, d F Y") ?></span>
        </div>
        <div style="float: right; padding-right: 200px;">
            <b style="font-size: 40px !important; font-family: Orbitron;" class="jam">23:00:54</b>
        </div>
    </div>
</body>
<script>
    $(function() {
        setTimeout(function() {
            window.location.href = window.location;
        }, 30000);

        setInterval(jam, 1000);
    });

    function jam() {
        var time = new Date(),
            hours = time.getHours(),
            minutes = time.getMinutes(),
            seconds = time.getSeconds();
        document.querySelectorAll('.jam')[0].innerHTML = harold(hours) + ":" + harold(minutes) + ":" + harold(seconds);

        function harold(standIn) {
            if (standIn < 10) {
                standIn = '0' + standIn
            }
            return standIn;
        }
    }
</script>