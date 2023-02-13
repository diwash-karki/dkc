<?php
try {
?>
    <?php include_once('../backend/adminsession.php'); ?>
    <?php
    include '../backend/config/conifg.php';
    $web = $config->fetch();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Admin Borrower | <?php echo $web["name"]; ?></title>

    </head>
    <link rel="manifest" href="manifest.json">
    <?php include "../global/links.html"; ?>
    <link rel="apple-touch-icon" href="./icon-192x192.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <link rel="stylesheet" type="text/css" href="../css/header.css">
    <link rel="stylesheet" type="text/css" href="../css/nav.css">
    <link rel="stylesheet" type="text/css" href="../css/buttons.css">
    <link rel="stylesheet" type="text/css" href="../css/footer.css">
    <link rel="stylesheet" type="text/css" href="../css/admin/borrower.css">
    <link rel="stylesheet" type="text/css" href="../css/topheader.css">
    <link rel="stylesheet" type="text/css" href="../css/alert.css">
    <?php include "../global/alert.html"; ?>
    <script type="text/javascript" src="../js/alert.js"></script>
    <script src="../js/borrower.js"></script>

    <?php include "../backend/summary/summaryControl.php"; ?>
    <?php
    include "../backend/connect.php";

    function get_details_from_username($conn, $username)
    {
        $sql = "SELECT * FROM investors WHERE username='$username'";
        $dd =  mysqli_fetch_array(mysqli_query($conn, $sql));
        if ($dd == null) return [];
        return $dd;
    }
    $c_summary = new Summary();
    $summary = $c_summary->allDatas($sum_conn);
    $monthsHeading = $c_summary->get_heading($sum_conn);
    $monthDatas = $c_summary->getMonthlyData($sum_conn);
    function get_diff($d1, $d2)
    {
        $m = explode("-", $d1);
        $m1 = explode("-", $d2);
        $nd = $m[2] . "/" . $m[0] . "/" . $m[1];
        $nd2 = $m1[2] . "/" . $m1[0] . "/" . $m1[1];
        $day = ((strtotime($nd) - strtotime($nd2)) / 86400);
        if ($day > 0) {
            return "expired";
        } else if (-30 <= ((strtotime($nd) - strtotime($nd2)) / 86400)) {
            return "expire-soon";
        } else {
            return "";
        }
    }

    function insertArrayAtPosition($array, $insert, $position)
    {
        /*
    $array : The initial array i want to modify
    $insert : the new array i want to add, eg array('key' => 'value') or array('value')
    $position : the position where the new array will be inserted into. Please mind that arrays start at 0
    */
        return array_slice($array, 0, $position, TRUE) + $insert + array_slice($array, $position, NULL, TRUE);
    }
    ?>

    <body>
        <?php include "../global/adminnav.php" ?>
        <?php include "../global/adminheader.php" ?>
        <?php
        include '../backend/usercontrol.php';
        $investors = $Users->category_users($conn, 2);
        $sid_collections = [];
        ?>
        <?php include '../backend/main/borrowerController.php'; ?>
        <div class="content">
            <div class="main-container">
                <div class="top-head-txt">
                    <div>
                        <p>Investors/Participants <br> <label class="sub-heading">You can add and update borrowers</label> </p>
                    </div>
                    <div>
                        <div class="search-bar">


                            <input class="search-box" autocomplete="false" list="investors-data" placeholder="  Investor Search.." name="search_usr" onchange="goto_find(this)">
                            <a href="" id="investor-goto"><button class="search-btn">FIND</button></a>
                        </div>

                        <datalist id="investors-data">
                            <?php
                            $searchData = $Users->category_users($conn, 2);
                            foreach ($searchData as $sdata) {
                            ?>
                                <option value="<?php echo $sdata["username"]; ?>">
                                <?php } ?>
                    </div>
                </div>
                <div class="top-head-button">
                    <button class="refresh_month_btn" title="Refresh Button" onclick="refresh_month()"> <i class="fa-solid fa-rotate"></i></button>
                    <button class="add_month_btn" title="Add Month Button" onclick="add_month()"> Add months </button>
                    <button class="refresh_month_btn" title="Expand Button" onclick="$('#expand').show()"><i class="fa-solid fa-expand"></i></button>
                </div>
                <section class="body-holder">
                    <?php
                    $inv_arr = [];
                    $summ_arr = [];
                    $servicehold = [];
                    $yieldhold = [];
                    $dkchold = [];

                    foreach ($summary as $sum) {
                        array_push($sid_collections, $sum["sid"]);

                        // if ($sum['dkcamt'] != "0") {

                        //     if (in_array('dkc', $inv_arr)) {
                        //         array_push($summ_arr['dkc'], $sum);
                        //     } else {

                        //         $summ_arr['dkc'] = [$sum];
                        //         array_push($inv_arr, 'dkc');
                        //     }
                        // }
                        if (floatval($sum['dkc']) != "None") {
                            if (in_array($sum['dkc'], $inv_arr)) {
                                array_push($summ_arr[$sum['dkc']], $sum);
                            } else {

                                $summ_arr[$sum['dkc']] = [$sum];
                                array_push($inv_arr, $sum['dkc']);
                            }
                        }
                        if (floatval($sum['servicingregular']) > 0) {
                            if (in_array('service', $inv_arr)) {
                                array_push($summ_arr['service'], $sum);
                            } else {

                                $summ_arr['service'] = [$sum];
                                array_push($inv_arr, 'service');
                            }
                        }
                        if (floatval($sum['yieldregular']) > 0) {
                            if (in_array('yield', $inv_arr)) {
                                array_push($summ_arr['yield'], $sum);
                            } else {

                                $summ_arr['yield'] = [$sum];
                                array_push($inv_arr, 'yield');
                            }
                        }

                        if ($sum['p1'] != "None") {

                            if (in_array($sum['p1'], $inv_arr)) {
                                array_push($summ_arr[$sum['p1']], $sum);
                            } else {

                                $summ_arr[$sum['p1']] = [$sum];
                                array_push($inv_arr, $sum['p1']);
                            }
                        }

                        if ($sum['p2'] != "None") {

                            if (in_array($sum['p2'], $inv_arr)) {
                                array_push($summ_arr[$sum['p2']], $sum);
                            } else {
                                array_push($inv_arr, $sum['p2']);
                                $summ_arr[$sum['p2']] = [$sum];
                            }
                        }
                        if ($sum['p3'] != "None") {
                            if (in_array($sum['p3'], $inv_arr)) {
                                array_push($summ_arr[$sum['p3']], $sum);
                            } else {
                                array_push($inv_arr, $sum['p3']);
                                $summ_arr[$sum['p3']] = [$sum];
                            }
                        }

                        if ($sum['p4'] != "None") {
                            if (in_array($sum['p4'], $inv_arr)) {
                                array_push($summ_arr[$sum['p4']], $sum);
                            } else {
                                array_push($inv_arr, $sum['p4']);
                                $summ_arr[$sum['p4']] = [$sum];
                            }
                        }
                    }


                    ?>
                    <?php
                    $servicehold = $summ_arr['service'];
                    $yieldhold = $summ_arr['yield'];
                    $dkchold = $summ_arr['DKC'];
                    unset($summ_arr['DKC']);
                    unset($summ_arr['service']);
                    unset($summ_arr['yield']);
                    $tempSort = [];
                    $tempSort['DKC Lending LLC'] = $dkchold;

                    for ($i = 0; $i < count($investors); $i++) {
                        $tempSort[$investors[$i]["username"]] = $summ_arr[$investors[$i]["username"]];
                    }
                    $summ_arr = $tempSort;

                    $summ_arr = insertArrayAtPosition($summ_arr, ['DKC Servicing Fee Income' => $servicehold], 1);
                    $summ_arr = insertArrayAtPosition($summ_arr, ['DKC Yield Spread Income' => $yieldhold], 2);


                    // echo json_encode($summ_arr);

                    foreach ($summ_arr as $head => $datas) {
                        $sum_month = [];

                        $udetails = get_details_from_username($conn, $head);

                        if ($datas != null) {
                    ?>
                            <div class="investor-card">
                                <section>
                                    <div>
                                        <h4>
                                            <a href="search.php?search_usr=<?php echo $head; ?>">
                                                <?php if (count($udetails) > 0) {
                                                    echo $udetails["fname"] . " " . $udetails["lname"];
                                                } else {
                                                    echo $head;
                                                }; ?>
                                            </a>
                                        </h4>
                                    </div>

                                    <table class="investor-table" id="<?php echo $head ?>">

                                        <thead>
                                            <tr>

                                                <th>Link</th>
                                                <th onclick="sortTable('<?php echo $head; ?>',1)">Borrower LLC</th>
                                                <th onclick="sortTable('<?php echo $head; ?>',2)">Collateral Address</th>

                                                <th onclick="sortTable('<?php echo $head; ?>',3)">Total Loan Amount</th>
                                                <th onclick="sortTable('<?php echo $head; ?>',4)">Investor Equity</th>
                                                <th>Interest Rate</th>
                                                <th onclick="sortTable('<?php echo $head; ?>',6)">Regular Payment</th>
                                                <th onclick="sortTable('<?php echo $head; ?>',7)">Origination Date</th>
                                                <th onclick="sortTable('<?php echo $head; ?>',8)">Maturity Date</th>
                                                <?php
                                                $cnt = 9;
                                                foreach ($monthsHeading as $he) {
                                                ?>
                                                    <th onclick="sortTable('<?php echo $head; ?>','<?php echo $cnt; ?>')"> <?php echo $he; ?></th>
                                                <?php
                                                    $cnt++;
                                                }

                                                ?>

                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $total_amt = 0;
                                            $total_regular = 0;
                                            $total_pay = 0;

                                            foreach ($datas as $data) {

                                                if (floatval($data['dkc']) != '') {
                                                    $part = "dkc";
                                                }
                                                if ($data['p1'] == $head) {
                                                    $part = "p1";
                                                }
                                                if ($data['p2'] == $head) {
                                                    $part = "p2";
                                                }
                                                if ($data['p3'] == $head) {
                                                    $part = "p3";
                                                }
                                                if ($data['p4'] == $head) {
                                                    $part = "p4";
                                                }
                                                $total_amt += ($data['tloan']);
                                                $total_regular += $data["$part" . "amt"];
                                                $total_pay += $data["$part" . "regular"];

                                            ?>
                                                <?php
                                                $exp = "";
                                                $class = get_diff(date("m-d-Y"), $data['mdate']);
                                                ?>
                                                <tr class="<?php echo ($data['tloan'] > 0) ? "" : "paidoffred" ?>">
                                                    <td><a href="<?php echo $data['link'] ?>" target="_blank">Link</td>
                                                    <td><?php echo $data['bllc'] ?></td>
                                                    <td><?php echo $data['bcoll'] ?></td>
                                                    <td value=<?php echo $data['tloan']; ?>><?php echo "$" .  number_format(floatval($data['tloan']), 2) ?></td>
                                                    <td value=<?php echo $data["$part" . "amt"]; ?>><?php echo "$" .  number_format(floatval($data["$part" . "amt"]), 2) ?></td>
                                                    <td><?php echo $data["$part" . "rate"] . "%" ?></td>
                                                    <td value=<?php echo $data["$part" . "regular"]; ?>><?php echo "$" .  number_format(floatval($data["$part" . "regular"]), 2); ?></td>
                                                    <td value=<?php echo strtotime($data["odate"]); ?>><?php echo $data["odate"] ?></td>
                                                    <td class="<?php echo $class; ?>"><?php echo $data["mdate"] ?></td>
                                                    <?php

                                                    foreach ($monthsHeading as $t_head) {
                                                        foreach ($monthDatas as $d) {
                                                            $head = ($head == 'DKC Lending LLC') ? "DKC" : $head;
                                                            $head = ($head == 'DKC Servicing Fee Income') ? "service" : $head;
                                                            $head = ($head == 'DKC Yield Spread Income') ? "yield" : $head;

                                                            if ($d['sumid'] == $data['sid'] && $d['investor'] == $head) {

                                                                if ($d[$t_head] != null) {


                                                                    if (in_array($t_head, array_keys($sum_month))) {
                                                                        $sum_month[$t_head] += floatval($d[$t_head]);
                                                                    } else {
                                                                        $sum_month[$t_head] += floatval($d[$t_head]);
                                                                    }
                                                    ?>

                                                    <?php

                                                                    echo "<td value=" . $d[$t_head] . ">" . "$" . number_format(floatval($d[$t_head]), 2) . "</td>";
                                                                } else {
                                                                    $sum_month[$t_head] += 0;
                                                                    echo '<td value=0> $0.00 </td>';
                                                                }
                                                            }
                                                        }
                                                    }




                                                    ?>
                                                </tr>
                                            <?php
                                            }

                                            ?>
                                            <tr class="total_tr" id="<?php echo $head . '_total_tr' ?>">
                                                <td>Total <?php echo count($datas); ?></td>
                                                <td></td>
                                                <td></td>
                                                <td><?php echo "$" . number_format(floatval($total_amt), 2) ?></td>
                                                <td><?php echo "$" . number_format(floatval($total_regular), 2) ?></td>
                                                <td></td>
                                                <td><?php echo "$" . number_format(floatval($total_pay), 2) ?></td>
                                                <td></td>
                                                <td></td>
                                                <?php
                                                foreach ($sum_month as $month) {
                                                    echo '<td> $' . number_format(floatval($month), 2) . '</td>';
                                                }
                                                ?>
                                            </tr>


                                        </tbody>
                                    </table>
                                </section>
                            </div>

                    <?php
                        }
                    }

                    ?>
                    <?php
                    $tpayable = [];
                    $tcollect = [];
                    $tservice = [];
                    $tyield = [];
                    $columns = [];
                    // $sql = "show columns from months";
                    // $rslt = mysqli_query($sum_conn, $sql);

                    // while ($row = $rslt->fetch_assoc()) {
                    //     if (!in_array($row['Field'], ["mid", "sumid", "investor"])) {
                    //         $columns[] = $row['Field'];
                    //     }
                    // }
                    $columns = $c_summary->get_heading($sum_conn);
                    $sql = "SELECT * FROM months";
                    $rslt = mysqli_query($sum_conn, $sql);
                    while ($datas = mysqli_fetch_array($rslt)) {

                        if (in_array($datas["sumid"], $sid_collections)) {
                            foreach ($columns as $title) {

                                if ($datas['investor'] != 'service' && $datas['investor'] != 'yield') {

                                    $tpayable[$title] += $datas[$title];
                                } else {
                                    if ($datas['investor'] == "service") {
                                        $tservice[$title] += $datas[$title];
                                    }
                                    if ($datas['investor'] == "yield") {
                                        $tyield[$title] += $datas[$title];
                                    }
                                }
                                $tcollect[$title] += $datas[$title];
                            }
                        }
                    }
                    ?>
                    <br>
                    <br>
                    <table class="main-total" style="background-color:white;">
                        <tr>
                            <th style="background-color:white;"></th>
                            <?php
                            foreach ($monthsHeading as $head) {
                                echo '<th style="background-color:white;">' . $head . '</th>';
                            }
                            ?>

                        </tr>
                        <tr>


                            <td><label style="margin-right:20px;">Total Participants Payable :</label> </td>
                            <?php
                            foreach ($tpayable as $tp) {
                            ?>
                                <td><?php echo "$" . number_format($tp, 2); ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><label style="margin-right:20px;">Total Collectable :</label> </td>
                            <?php
                            foreach ($tcollect as $tp) {
                            ?>
                                <td><?php echo "$" . number_format($tp, 2); ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><label style="margin-right:20px;">Balance :</label> </td>
                            <?php
                            foreach ($columns as $title) {

                            ?>
                                <td><?php echo "$" . number_format(floatval($tcollect[$title]) - floatval($tpayable[$title]), 2); ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><label style="margin-right:20px;">Servicing Fee :</label> </td>
                            <?php
                            foreach ($tservice as $tp) {
                            ?>
                                <td><?php echo "$" . number_format($tp, 2); ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><label style="margin-right:20px;">Yield Spread :</label> </td>
                            <?php
                            foreach ($tyield as $tp) {
                            ?>
                                <td><?php echo "$" . number_format($tp, 2); ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><label style="margin-right:20px;">Check/Balance :</label> </td>
                            <?php
                            foreach ($columns as $title) {
                            ?>
                                <td style="background-color: #33CAFF !important"><?php echo "$" . number_format((floatval($tcollect[$title]) - floatval($tpayable[$title])) - (floatval($tservice[$title]) + floatval($tyield[$title])), 2); ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                    </table>

                </section>
            </div>

            <div id="expand" class="expand">
                <table border="1" id="expand-table">
                    <tr>
                        <th onclick="sortTable('expand-table',0)">sn</th>
                        <th onclick="sortTable('expand-table',1)">Borrower LLC</th>
                        <th onclick="sortTable('expand-table',2)">Full Name</th>
                        <th onclick="sortTable('expand-table',3)">Collateral Address</th>
                        <th onclick="sortTable('expand-table',4)">Total Loan</th>
                        <th>Interest %</th>
                        <th onclick="sortTable('expand-table',6)">Origin. Date</th>
                        <th onclick="sortTable('expand-table',7)">Maturity Date</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th onclick="sortTable('expand-table',10)">Insurance Exp.</th>
                        <th onclick="sortTable('expand-table',11)">ACH</th>
                        <th>Service</th>
                        <th onclick="sortTable('expand-table',13)">Lender</th>
                        <th onclick="sortTable('expand-table',14)">Lender. equity</th>
                        <th>Lender Rate</th>
                        <th onclick="sortTable('expand-table',16)">Lender Prorated</th>
                        <th onclick="sortTable('expand-table',17)">Lender Regular</th>
                        <th onclick="sortTable('expand-table',18)">P1</th>
                        <th onclick="sortTable('expand-table',19)">P1. equity</th>
                        <th>P1 Rate</th>
                        <th onclick="sortTable('expand-table',21)">P1 Prorated</th>
                        <th onclick="sortTable('expand-table',22)">P1 Regular</th>
                        <th onclick="sortTable('expand-table',23)">p2</th>
                        <th onclick="sortTable('expand-table',24)">p2. equity</th>
                        <th>p2 Rate</th>
                        <th onclick="sortTable('expand-table',26)">p2 Prorated</th>
                        <th onclick="sortTable('expand-table',27)">p2 Regular</th>
                        <th onclick="sortTable('expand-table',28)">P3</th>
                        <th onclick="sortTable('expand-table',29)">P3. equity</th>
                        <th>P3 Rate</th>
                        <th onclick="sortTable('expand-table',31)">P3 Prorated</th>
                        <th onclick="sortTable('expand-table',32)">P3 Regular</th>
                        <th onclick="sortTable('expand-table',33)">P4</th>
                        <th onclick="sortTable('expand-table',34)">P4. equity</th>
                        <th>P4 Rate</th>
                        <th onclick="sortTable('expand-table',36)">P4 Prorated</th>
                        <th onclick="sortTable('expand-table',37)">P4 Regular</th>
                        <th onclick="sortTable('expand-table',38)">Service</th>
                        <th onclick="sortTable('expand-table',39)">Service. equity</th>
                        <th>Service Rate</th>
                        <th onclick="sortTable('expand-table',41)">Service Prorated</th>
                        <th onclick="sortTable('expand-table',42)">Service Regular</th>
                        <th onclick="sortTable('expand-table',43)">Yield</th>
                        <th onclick="sortTable('expand-table',44)">Yield. equity</th>
                        <th>Yield Rate</th>
                        <th onclick="sortTable('expand-table',46)">Yield Prorated</th>
                        <th onclick="sortTable('expand-table',47)">Yield Regular</th>
                        <th onclick="sortTable('expand-table',48)">Balance</th>
                    </tr>

                    <?php
                    $sn = 1;
                    foreach ($summary as $s) {

                    ?>
                        <tr <?php echo (intval($s['status'])  != 1) ? 'class="paidoff-tr"' : '' ?>>
                            <td><?php echo $sn; ?></td>
                            <td><?php echo $s['bllc']; ?></td>
                            <td><?php echo $s['fname'] . " " . $s['lname']; ?></td>
                            <td><a href="<?php echo $s['link']; ?>" target="_blank"><?php echo $s['bcoll']; ?></a></td>
                            <td class="<?php echo floatval($s['tloan']) ?>"><?php echo "$" . number_format(floatval($s['tloan']), 2); ?></td>
                            <td><?php echo $s['irate'] . "%"; ?></td>
                            <td><?php echo $s['odate']; ?></td>
                            <td><?php echo $s['mdate']; ?></td>
                            <td><?php echo $s['bphone']; ?></td>
                            <td><?php echo $s['bemail']; ?></td>
                            <td><a href="<?php echo $s['taxurl'] ?>" target="_blank"><?php echo $s['iexpiry']; ?></a></td>
                            <td><?php echo $s['ach']; ?></td>
                            <td><?php echo $s['service']; ?></td>

                            <td><?php echo $s['dkc']; ?></td>
                            <td class="<?php echo floatval($s['dkcamt']); ?>"><?php echo "$" . number_format(floatval($s['dkcamt']), 2); ?></td>
                            <td><?php echo $s['dkcrate']; ?></td>
                            <td class="<?php echo floatval($s['dkcprorated']); ?>"><?php echo "$" . number_format(floatval($s['dkcprorated']), 2); ?></td>
                            <td class="<?php echo floatval($s['dkcregular']); ?>"><?php echo "$" . number_format(floatval($s['dkcregular']), 2); ?></td>

                            <td><?php echo $s['p1']; ?></td>
                            <td class="<?php echo floatval($s['p1amt']); ?>"><?php echo "$" . number_format(floatval($s['p1amt']), 2); ?></td>
                            <td><?php echo $s['p1rate']; ?></td>
                            <td class="<?php echo floatval($s['p1prorated']); ?>"><?php echo "$" . number_format(floatval($s['p1prorated']), 2); ?></td>
                            <td class="<?php echo floatval($s['p1regular']); ?>"><?php echo "$" . number_format(floatval($s['p1regular']), 2); ?></td>

                            <td><?php echo $s['p2']; ?></td>
                            <td class="<?php echo floatval($s['p2amt']); ?>"><?php echo "$" . number_format(floatval($s['p2amt']), 2); ?></td>
                            <td><?php echo $s['p2rate']; ?></td>
                            <td class="<?php echo floatval($s['p2prorated']); ?>"><?php echo "$" . number_format(floatval($s['p2prorated']), 2); ?></td>
                            <td class="<?php echo floatval($s['p2regular']); ?>"><?php echo "$" . number_format(floatval($s['p2regular']), 2); ?></td>

                            <td><?php echo $s['p3']; ?></td>
                            <td class="<?php echo floatval($s['p3amt']); ?>"><?php echo "$" . number_format(floatval($s['p3amt']), 2); ?></td>
                            <td><?php echo $s['p3rate']; ?></td>
                            <td class="<?php echo floatval($s['p3prorated']); ?>"><?php echo "$" . number_format(floatval($s['p3prorated']), 2); ?></td>
                            <td class="<?php echo floatval($s['p3regular']); ?>"><?php echo "$" . number_format(floatval($s['p3regular']), 2); ?></td>

                            <td><?php echo $s['p4']; ?></td>
                            <td class="<?php echo floatval($s['p4amt']); ?>"><?php echo "$" . number_format(floatval($s['p4amt']), 2); ?></td>
                            <td><?php echo $s['p4rate']; ?></td>
                            <td class="<?php echo floatval($s['p4prorated']); ?>"><?php echo "$" . number_format(floatval($s['p4prorated']), 2); ?></td>
                            <td class="<?php echo floatval($s['p4regular']); ?>"><?php echo "$" . number_format(floatval($s['p4regular']), 2); ?></td>
                            <td>Service</td>
                            <td class="<?php echo floatval($s['servicingamt']); ?>"><?php echo "$" . number_format(floatval($s['servicingamt']), 2); ?></td>
                            <td><?php echo $s['servicingrate']; ?></td>
                            <td class="<?php echo floatval($s['servicingprorated']); ?>"><?php echo "$" . number_format(floatval($s['servicingprorated']), 2); ?></td>
                            <td class="<?php echo floatval($s['servicingregular']); ?>"><?php echo "$" . number_format(floatval($s['servicingregular']), 2); ?></td>
                            <td>Yield</td>
                            <td class="<?php echo floatval($s['yieldamt']); ?>"><?php echo "$" . number_format(floatval($s['yieldamt']), 2); ?></td>
                            <td><?php echo $s['yieldrate']; ?></td>
                            <td class="<?php echo floatval($s['yieldregular']); ?>"><?php echo "$" . number_format(floatval($s['yieldregular']), 2); ?></td>
                            <td class="<?php echo floatval($s['yieldprorated']); ?>"><?php echo "$" . number_format(floatval($s['yieldprorated']), 2); ?></td>

                            <td class="<?php echo floatval($s['balance']); ?>"><?php echo "$" . number_format(floatval($s['balance']), 2); ?></td>

                        <?php
                        $sn++;
                    }
                        ?>
                </table>

            </div>
            <?php include "../global/footer.php"; ?>
        </div>

    </body>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('serviceWorker.js').then(function(registration) {
                    console.log('Worker registration successful', registration.scope);
                }, function(err) {
                    console.log('Worker registration failed', err);
                }).catch(function(err) {
                    console.log(err);
                });
            });
        } else {
            console.log('Service Worker is not supported by browser.');
        }
    </script>

    </html>

<?php
} catch (Error $er) {
    ob_clean();
    include('../500.php');
} finally {
    ob_flush();
}
?>