<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS - IP RANGES</title>
    <link rel="shortcut icon" href="https://leafphp.dev/logo-circle.png" type="image/x-icon">
    <link rel="stylesheet" href="{{ PublicPath('assets/css/styles.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700;display=swap">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
</head>
<script>

    function find_prefix(element) {
        // get prefix string from input
        const prefix = 'prefix-' + ''.replace(/\.|:|\//gi, '-');
        const id = $('#' + prefix);
        id.addClass('active-prefix');
        $('html, body').animate({
            scrollTop: id.offset().top
        }, 1000)
        setTimeout(() => {
            id.removeClass('active-prefix')
        }, 5000);
    }

    function show_snack_bar(text) {
        const snackbar = $('#snackbar');
        if (!snackbar.hasClass('show')) {
            snackbar.addClass('show');
            snackbar.text(text);
            setTimeout(() => {
                snackbar.removeClass('show')
            }, 3000);
        }
    }

    function copy_prefix(element) {
        const prefix = $(element).text();
        show_snack_bar("Copied to the clipboard");
        console.log("Copied to the clipboard: " + prefix);
        navigator.clipboard.writeText(prefix);
    }

    $(window).ready(() => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                console.log(entry.intersectionRatio);
                let nav_element = $('#' + entry.target.id.toLowerCase() + '-nav-item');
                if (entry.isIntersecting) {
                    nav_element.addClass('active');
                } else {
                    nav_element.removeClass('active');
                }
            });
        }, {threshold: [0.15]});

        @foreach ($zones as $zone)
        observer.observe(document.querySelector("#{{$zone['id']}}"));
        @endforeach
    })
</script>
<body class="flex h-screen">
<header>
    <h1>EXITLAG</h1>
</header>
<section class="navigator-menu">
    <h2>AWS ZONES</h2>
    @foreach ($zones as $zone)
        <a id="{{$zone['id']}}-nav-item" href="#{{$zone['id']}}" title="{{$zone['id']}}">
            <p>{{$zone['location']}}</p>
            <span>●</span>
        </a>
    @endforeach
</section>
<main>
    <div class="main-container">
        <section class="filters-container">
            <button onclick="find_prefix(this)">CLIQUE AQUI</button>

            <section>
                <label for="filter-protocol">Protocol:</label>
                <select id="filter-protocol" name="protocol">
                    <option value="" disabled selected hidden>All</option>
                    <option value="ipv4">IPV4</option>
                    <option value="ipv6">IPV6</option>
                </select>
            </section>
            <section>
                <label for="filter-zone">Zone:</label>
                <select id="filter-zone" name="zone">
                    <option value="" disabled selected hidden>All</option>
                    @foreach ($ipranges as $zone_id => $content)
                        <option value="{{$zone_id}}">{{$content[0]['location']}} ({{$zone_id}})</option>
                    @endforeach
                </select>
            </section>
            <button class="ok-button" title="Apply filters">OK</button>
            <button class="nok-button" title="Clear filters">Reset <span>&#10227</span></button>
        </section>
        <table>
            <thead>
            <tr>
                <th>Zone ID</th>
                <th>Location</th>
                <th>Address Prefix</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($ipranges as $region_id => $content)
                <tr>
                    <td id="{{strtolower($region_id)}}">
                        <div>
                            {{$region_id}}
                        </div>
                    </td>
                    <td>{{$content[0]['location']}}</td>
                    <td class="prefixes-column">
                        @foreach ($content as $prefix)
                            <p onclick="copy_prefix(this)"
                               id="prefix-{{str_replace(array('.', ':', '/'), '-', $prefix['prefix'])}}">{{$prefix['prefix']}}</p>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <section id="snackbar"></section>
</main>
</body>
</html>
