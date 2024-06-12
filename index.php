<?php
ob_start();
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <link rel="apple-touch-icon" sizes="180x180" href="image/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="image/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="image/favicon/favicon-16x16.png">
    <link rel="manifest" href="image/favicon/site.webmanifest">
    <link rel="mask-icon" href="image/favicon/safari-pinned-tab.svg" color="#5bbad5">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.5/css/select.dataTables.min.css">
    <link rel="stylesheet" href="css/main.css">

    <?php

    require 'lib/phpPasswordHashing/passwordLib.php';
    require 'app/DB.php';
    require 'app/Util.php';
    require 'app/dao/CustomerDAO.php';
    require 'app/dao/BookingDetailDAO.php';
    require 'app/models/RequirementEnum.php';
    require 'app/models/Customer.php';
    require 'app/models/Booking.php';
    require 'app/models/Reservation.php';
    require 'app/handlers/CustomerHandler.php';
    require 'app/handlers/BookingDetailHandler.php';

    $username = $cHandler = $bdHandler = $cBookings = null;
    $isSessionExists = false;
    $isAdmin = [];
    if (isset($_SESSION["username"])) {
        $username = $_SESSION["username"];

        $cHandler = new CustomerHandler();
        $cHandler = $cHandler->getCustomerObj($_SESSION["accountEmail"]);
        $cAdmin = new Customer();
        $cAdmin->setEmail($cHandler->getEmail());

        $bdHandler = new BookingDetailHandler();
        $cBookings = $bdHandler->getCustomerBookings($cHandler);
        $isSessionExists = true;
        $isAdmin = $_SESSION["authenticated"];
    }
    if (isset($_SESSION["isAdmin"]) && isset($_SESSION["username"])) {
        $isSessionExists = true;
        $username = $_SESSION["username"];
        $isAdmin = $_SESSION["isAdmin"];
    }

    // if (isset($_COOKIE['is_admin'])) {
    //     echo $_COOKIE['is_admin'];
    //     var_dump($isAdmin);
    // }

    ?>
    <title>UNEJ CONNECT</title>
    <?php //echo '<title>Home isAdmin=' . $isAdmin . ' $isSessionExists=' . $isSessionExists . '</title>'?>
</head>
<body>

<header>
    <div class="bg-secondary collapse" id="navbarHeader">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-md-7 py-4">
                    <h4 class="text-white"></h4>
                    <p class="text-muted"></p>
                </div>
                <div class="col-sm-4 offset-md-1 py-4 text-right">
                    <?php if ($isSessionExists) { ?>
                    <h4 class="text-white"><?php echo $username; ?></h4>
                    <ul class="list-unstyled">
                        <?php if ($isAdmin[1] == "true" && isset($_COOKIE['is_admin']) && $_COOKIE['is_admin'] == "true") { ?>
                        <li><a href="admin.php" class="text-white">Reservasi Gedung<i class="far fa-address-book ml-2"></i></a></li>
                        <?php } else { ?>
                        <li><a href="#" class="text-white my-reservations">Lihat Reservasi Gedung<i class="far fa-address-book ml-2"></i></a></li>
                        <li>
                            <a href="#" class="text-white" data-toggle="modal" data-target="#myProfileModal">Profil Saya<i class="fas fa-user ml-2"></i></a>
                        </li>
                        <?php } ?>
                        <li><a href="#" id="sign-out-link" class="text-white">Log out<i class="fas fa-sign-out-alt ml-2"></i></a></li>
                    </ul>
                    <?php } else { ?>
                    <h4>
                        <a class="text-white" href="sign-in.php">Masuk</a> <span class="text-white">atau</span>
                        <a href="register.php" class="text-white">Daftar</a>
                    </h4>
                    <p class="text-muted">Silahkan login terlebih dahulu untuk dapat mengakses fitur UNEJ CONNECT</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="navbar navbar-dark bg-secondary box-shadow">
        <div class="container d-flex justify-content-between">
            <a href="#" class="navbar-brand d-flex align-items-center">
                <!-- <i class="fas fa-h-square mr-2"></i> -->
                <strong>UNEJ CONNECT</strong>
            </a>
            <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
    <div class="container my-3" id="my-reservations-div">
        <h4>Reservasi Gedung</h4>
        <table id="myReservationsTbl" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th class="text-hide p-0" data-bookId="12">12</th>
                <th scope="col">Tanggal Mulai</th>
                <th scope="col">Tanggal Selesai</th>
                <th scope="col">Jenis Ruangan</th>
                <th scope="col">Kebutuhan</th>
                <th scope="col">Jumlah Panitia</th>
                <th scope="col">Jumlah Peserta</th>
                <th scope="col">Requests</th>
                <th scope="col">Timestamp</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($cBookings) && $bdHandler->getExecutionFeedback() == 1) { ?>
                <?php   foreach ($cBookings as $k => $v) { ?>
                    <tr>
                        <th scope="row"><?php echo ($k + 1); ?></th>
                        <td class="text-hide p-0"><?php echo $v["id"]; ?></td>
                        <td><?php echo $v["start"]; ?></td>
                        <td><?php echo $v["end"]; ?></td>
                        <td><?php echo $v["type"]; ?></td>
                        <td><?php echo $v["requirement"]; ?></td>
                        <td><?php echo $v["adults"]; ?></td>
                        <td><?php echo $v["children"]; ?></td>
                        <td><?php echo $v["requests"]; ?></td>
                        <td><?php echo $v["timestamp"]; ?></td>
                        <td><?php echo $v["status"]; ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
    </div>
</header>

<!-- Hero pertama -->
<main role="main">

    <section class="jumbotron text-center bg-white">
        <div class="container pt-lg-5 pl-5 px-5">
            <h1 class="display-3">UNEJ CONNECT</h1>
            <p class="lead text-muted">Tempat dimana reservasi Gedung di UNEJ menjadi lebih cepat dan mudah </p>
            <p>
                <?php if ($isSessionExists) { ?>
                <a href="#" class="btn btn-success my-2" data-toggle="modal" data-target=".book-now-modal-lg">Reservasi Sekarang<i class="fas fa-angle-double-right ml-1"></i></a>
                <?php } else { ?>
                <a href="#" class="btn btn-success my-2" data-toggle="modal" data-target=".sign-in-to-book-modal">Reservasi Sekarang<i class="fas fa-angle-double-right ml-1"></i></a>
                <?php } ?>
            </p>
        </div>
    </section>

    <div class="container">
        <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
            <h1 class="display-4">PILIHAN GEDUNG</h1>
            <p class="lead">di bawah ini merupakan pilihan gedung yang bisa anda pilih</p>
        </div>
    </div>

    <div class="album py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <div class="card-header">
                            <h5 class="my-0 font-weight-normal">Auditorium UNEJ</h5>
                        </div>
                        <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" style="height: 225px; width: 100%; display: block;" src="image/audit-unej.jpg" data-holder-rendered="true">
                        <div class="card-body">
                            <p class="card-text">Auditorium merupakan suatu tempat luas, dan biasa digunakan untuk kegiatan besar pada aktivitas yang berkaitan dengan perkuliahan, seperti seminar dan acara besar lainnya. Auditorium dapat menampung audiens yang cukup banyak, dikarenakan memiliki kapasitas tempat duduk yang besar sehingga memungkinkan untuk mengakomodasi audiens yang banyak.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <?php if ($isSessionExists) { ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-rtype="Deluxe" data-toggle="modal" data-target=".book-now-modal-lg">
                                        Book
                                    </button>
                                    <?php } else { ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target=".sign-in-to-book-modal">
                                        Book
                                    </button>
                                    <?php } ?>
                                </div>
                                <!-- <small class="text-muted">$250 / night</small> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <div class="card-header">
                            <h5 class="my-0 font-weight-normal">Stadion UNEJ</h5>
                        </div>
                        <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="image/stadion.jpeg" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
                        <div class="card-body">
                            <p class="card-text">stadion dengan kapasitas yang luas dan tribun yang menghadap ke lapangan, stadion ini menjadi pusat kegiatan atletik dan kebudayaan di Universitas Jember. Rumput hijau yang terawat dengan baik memberikan latar belakang yang sempurna untuk pertandingan sepak bola, atletik, dan acara olahraga lainnya. Dari turnamen antar-fakultas hingga konser musik dan festival budaya, stadion ini menjadi tempat yang ramai dengan energi positif</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if ($isSessionExists) { ?>
                                <button type="button" class="btn btn-sm btn-outline-success" data-rtype="Double" data-toggle="modal" data-target=".book-now-modal-lg">
                                    Book
                                </button>
                                <?php } else { ?>
                                <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target=".sign-in-to-book-modal">
                                    Book
                                </button>
                                <?php } ?>
                                <!-- <small class="text-muted">$180 / night</small> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <div class="card-header">
                            <h5 class="my-0 font-weight-normal">Lapangan PKM</h5>
                        </div>
                        <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="image/pkm.PNG" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
                        <div class="card-body">
                            <p class="card-text">Lapangan pkm merupakan tempat yang menyenangkan bagi siapa pun yang ingin bermain basket di kampus.Lapangan ini cocok untuk berolahraga dan bersantai. Di sini, kamu bisa bermain basket dengan teman-temanmu atau bergabung dalam pertandingan antar-fakultas. Dengan fasilitas yang lengkap dan suasana yang menyenangkan</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if ($isSessionExists) { ?>
                                <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-rtype="Single" data-target=".book-now-modal-lg">
                                    Book
                                </button>
                                <?php } else { ?>
                                <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target=".sign-in-to-book-modal">
                                    Book
                                </button>
                                <?php } ?>
                                <!-- <small class="text-muted">$130 / night</small> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- FORM RESERVASI -->
    <?php if(isset($_COOKIE['is_admin']) && $_COOKIE['is_admin'] == "false") : ?>
    <div class="modal fade book-now-modal-lg" tabindex="-1" role="dialog" aria-labelledby="bookNowModalLarge" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">FORM RESERVASI</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" id="reservationModalBody">
                    <?php if ($isSessionExists == 1 && $isAdmin[1] == "false") { ?>
                        <form role="form" autocomplete="off" method="post" id="multiStepRsvnForm">
                            <div class="rsvnTab">
                                <?php if ($isSessionExists) { ?>
                                    <input type="number" name="cid" value="<?php echo $cHandler->getId() ?>" hidden>
                                <?php } ?>
                                <div class="form-group row">
                                    <label for="startDate" class="col-sm-3 col-form-label">Tanggal Mulai
                                        <span class="red-asterisk"> *</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fa fa-calendar"></i>
                                                </span>
                                            </div>
                                            <input type="date" class="form-control"
                                                   name="startDate"  min="<?php echo Util::dateToday('0'); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="endDate" class="col-sm-3 col-form-label">Tanggal Selesai
                                        <span class="red-asterisk"> *</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="inputGroupPrepend">
                                                    <i class="fa fa-calendar"></i>
                                                </span>
                                            </div>
                                            <input type="date" class="form-control"  min="<?php echo Util::dateToday('1'); ?>" name="endDate" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label" for="roomType">Tipe Gedung
                                        <span class="red-asterisk"> *</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <select required class="custom-select mr-sm-2"  name="roomType">
                                            <option value="<?php echo \models\RequirementEnum::AUDITORIUM; ?>">Auditorium UNEJ</option>
                                            <option value="<?php echo \models\RequirementEnum::STADION; ?>">Stadion UNEJ</option>
                                            <option value="<?php echo \models\RequirementEnum::LAPANGAN_PKM; ?>">Lapangan PKM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label" for="roomRequirement">Kebutuhan</label>
                                    <div class="col-sm-9">
                                        <select class="custom-select mr-sm-2"  name="roomRequirement">
                                            <option value="tidak perlu" selected>tidak perlu</option>
                                            <option value="satpam">Satpam</option>
                                            <option value="petugas kebersihan">Petugas Kebersihan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label" for="adults">Jumlah Panitia
                                        <span class="red-asterisk"> *</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <select required class="custom-select mr-sm-2"  name="adults">
                                            <option selected value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label" for="children">Jumlah Peserta</label>
                                    <div class="col-sm-9">
                                        <select class="custom-select mr-sm-2"  name="children">
                                            <option selected value="0">-</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                            <option value="32">32</option>
                                            <option value="33">33</option>
                                            <option value="34">34</option>
                                            <option value="35">35</option>
                                            <option value="36">36</option>
                                            <option value="37">37</option>
                                            <option value="38">38</option>
                                            <option value="39">39</option>
                                            <option value="40">40</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label" for="specialRequests">Keterangan</label>
                                    <div class="col-sm-9">
                                        <textarea rows="3" maxlength="500"  name="specialRequests" class="form-control"></textarea>
                                    </div>
                                </div>
                                <!-- <div class="form-group row align-items-center">
                                    <button type="button" class="btn btn-info" style="margin-left: 0.8em;" data-container="body" data-toggle="popover"
                                            data-placement="right" data-content="Check-in time starts at 3 PM. If a late check-in is planned, please contact our support department.">
                                        Peraturan
                                    </button>
                                </div> -->
                            </div>

                            <div class="rsvnTab">
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label font-weight-bold" for="bookedDate">Tanggal Booking</label>
                                    <div class="col-sm-9 bookedDateTxt">
                                        10 Juni, 2024
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label font-weight-bold" for="roomPrice">Harga Sewa</label>
                                    <div class="col-sm-9 roomPriceTxt">Rp. 1.000.000</div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label font-weight-bold" for="numNights"><span class="numNightsTxt">3</span> nights </label>
                                    <div class="col-sm-9">
                                        $<span class="roomPricePerNightTxt">69.63</span> avg. / night
                                    </div>
                                    <label class="col-sm-3 col-form-label font-weight-bold" for="numNights">From - to</label>
                                    <div class="col-sm-9 fromToTxt">
                                        Mon. July 4 to Wed. July 6
                                    </div>
                                    <label class="col-sm-3 col-form-label font-weight-bold">Taxes </label>
                                    <div class="col-sm-9">
                                        $<span class="taxesTxt">0</span>
                                    </div>
                                    <label class="col-sm-3 col-form-label font-weight-bold">Total </label>
                                    <div class="col-sm-9">
                                        $<span class="totalTxt">0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div style="text-align:center;margin-top:40px;">
                                <span class="step"></span>
                                <span class="step"></span>
                            </div>

                        </form>
                        <div style="overflow:auto;">
                            <div style="float:right;">
                                <button type="button" class="btn btn-success" id="rsvnPrevBtn" onclick="rsvnNextPrev(-1)">Sebelumnya</button>
                                <button type="button" class="btn btn-success" id="rsvnNextBtn" onclick="rsvnNextPrev(1)" readySubmit="false">Next</button>
                            </div>
                        </div>
                    <?php } else { ?>
                        <p>Pemesanan disediakan untuk anda.</p>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal sign-in-to-book-modal" tabindex="-1" role="dialog" aria-labelledby="signInToBookModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Dibutuhkan login terlebih dahulu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4>silahkan <a href="sign-in.php">login</a>terlebih dahulu untuk bisa reservasi ruangan</h4>
                </div>
            </div>
        </div>
    </div>

    <?php if(($isSessionExists == 1 && $isAdmin[1] == "false") && isset($_COOKIE['is_admin']) && $_COOKIE['is_admin'] == "false") : ?>
    <div class="modal" id="myProfileModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card border-0">
                        <div class="card-body p-0">
                            <?php if ($isSessionExists) { ?>
                            <form class="form" role="form" autocomplete="off" id="update-profile-form" method="post">
                                <input type="number" id="customerId" hidden
                                       name="customerId" value="<?php echo $cHandler->getId(); ?>" >
                                <div class="form-group">
                                    <label for="updateFullName">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="updateFullName"
                                           name="updateFullName" value="<?php echo $cHandler->getFullName(); ?>" >
                                </div>
                                <div class="form-group">
                                    <label for="updatePhoneNumber">No. Telepon</label>
                                    <input type="text" class="form-control" id="updatePhoneNumber"
                                           name="updatePhoneNumber" value="<?php echo $cHandler->getPhone(); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="updateEmail">Email</label>
                                    <input type="email" class="form-control" id="updateEmail"
                                           name="updateEmail" value="<?php echo $cHandler->getEmail(); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="updatePassword">New Password</label>
                                    <input type="password" class="form-control" id="updatePassword"
                                           name="updatePassword"
                                           title="At least 4 characters with letters and numbers">
                                </div>
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary btn-md float-right"
                                           name="updateProfileSubmitBtn" value="Update">
                                </div>
                            </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<footer class="container">
    <p class="p-4">&copy; UNEJ Connect 2024</p>
</footer>



<script src="js/utilityFunctions.js"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js"
        integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/select/1.2.5/js/dataTables.select.min.js"></script>
<script src="js/animatejscx.js"></script>
<script src="js/form-submission.js"></script>
<script>
    $(document).ready(function () {
      let reservationDiv = $("#my-reservations-div");
      reservationDiv.hide();
      $(".my-reservations").click(function () {
        reservationDiv.slideToggle("slow");
      });
      $('#myReservationsTbl').DataTable();

      // dynamically entered room type value on show modal
      $('.book-now-modal-lg').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let roomType = button.data('rtype');
        let modal = $(this);
        modal.find('.modal-body select[name=roomType]').val(roomType);
      });

      // check-in policies popover
      $('[data-toggle="popover"]').popover();

    });
</script>
<script src="js/multiStepsRsvn.js"></script>
</body>
</html>