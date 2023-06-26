<?php

use App\Controllers\AwsController;

app()->get('/', function () {
    echo "<p>Click to go &#8594; <a href='/aws?protocol=ipv4'>/aws</a></p>";
});

app()->get('/aws', function () {
    $awsController = new AwsController();
    // capture url query parameters
    $protocol = request()->get("protocol");
    $service = request()->get("service");
    $region = request()->get("region");

    $response = $awsController->get();

    if ($response->status_code === 200) {
        $data = json_decode($response->body, true);
        if ($data) {
            if ($protocol === 'ipv4') {
                $regions = $awsController->aws_sort($data, [
                    "protocol" => $protocol,
                    "region" => $region,
                    "service" => $service,
                    "prefix" => array(
                        "collection" => "prefixes",
                        "key" => "ip_prefix"
                    )
                ]);
            } else if ($protocol === "ipv6") {
                $regions = $awsController->aws_sort($data, [
                    "protocol" => $protocol,
                    "region" => $region,
                    "service" => $service,
                    "prefix" => array(
                        "collection" => "ipv6_prefixes",
                        "key" => "ipv6_prefix"
                    )
                ]);
            } else {
                $ipv4_regions = $awsController->aws_sort($data, [
                    "protocol" => $protocol,
                    "region" => $region,
                    "service" => $service,
                    "prefix" => array(
                        "collection" => "prefixes",
                        "key" => "ip_prefix"
                    )
                ]);

                $ipv6_regions = $awsController->aws_sort($data, [
                    "protocol" => $protocol,
                    "region" => $region,
                    "service" => $service,
                    "prefix" => array(
                        "collection" => "ipv6_prefixes",
                        "key" => "ipv6_prefix"
                    )
                ]);

                $regions = array_merge_recursive($ipv4_regions, $ipv6_regions);
            }

            ksort($regions);
            $zone_names = array_map(function ($key) use($awsController) {
                return [
                    "id" => strtolower($key),
                    "location" => $awsController->get_zone_name($key)
                ];
            }, array_keys($regions));
            echo view("index", ["ipranges" => $regions, "zones" => $zone_names]);
        }
    }
});
