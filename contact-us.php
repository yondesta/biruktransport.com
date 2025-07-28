<?php include('form_process.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>St. Luke | Contact</title>
    <?php include "header.php"; ?>
    <style>
        .error { color: #ff0000; font-size: 0.9em; }
        .success { color: #008000; font-weight: bold; margin-top: 15px; }
    </style>
</head>

<body>
    

    <section id="contact-info">
        <div class="center">                
            <h2>How to Reach Us?</h2>
            <p class="lead">St. Luke Catholic Hospital and College of Nursing and Midwifery is located Wolisso, 114km far from the capital Addis Ababa to South West Shoa Zone, the road to Jimma.</p>
        </div>
        <div class="gmap-area">
            <div class="container">
                <div class="row">
                    <div class="col-sm-5 text-center">
                        <div class="gmap">
                            <iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1602114.0115954662!2d38.63796473900716!3d8.837119324008249!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xad88a92ea53fc68a!2sWolisso+Hospital!5e0!3m2!1sen!2sus!4v1480338648710"></iframe>
                        </div>
                    </div>
                    <div class="col-sm-7 map-content">
                        <ul class="row">
                            <li class="col-sm-10">
                                <address>
                                    <h4>St. Luke Catholic Hospital and College of Nursing and Midwifery</h4>
                                    Address : Wolisso South West Shoa Zone, The road to Jimma from Addis Ababa, Ethiopia. 
                                    <p>P.O.Box. 250, Wolisso, South West Shoa Zone , Ethiopia</p>
                                    <p>Phone: +251 - 11 - 341 - 0800 <br>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp +251 - 11 - 341 - 0150
                                    <br>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp +251 - 11 - 341 - 0714<br>
                                    Email Address : stluke.generalmanager@gmail.com</p>
                                </address>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact-page">
        <div class="container">
            <div class="center">        
                <h2>Drop Your Message</h2>
            </div> 
            <div class="row contact-wrap"> 
                <?php if (!empty($success)): ?>
                    <div class="col-md-8 col-md-offset-2 alert alert-success text-center">
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <form id="contact" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="form-horizontal">
                    <div class="col-sm-5 col-sm-offset-1">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" value="<?= $name ?>" class="form-control">
                            <span class="error"><?= $errors['name'] ?? '' ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?= $email ?>" class="form-control">
                            <span class="error"><?= $errors['email'] ?? '' ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?= $phone ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company" value="<?= $company ?>" class="form-control">
                        </div>                        
                    </div>
                    
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label>Subject *</label>
                            <input type="text" name="subject" value="<?= $subject ?>" class="form-control">
                            <span class="error"><?= $errors['subject'] ?? '' ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label>Message *</label>
                            <textarea name="message" class="form-control" rows="8"><?= $message ?></textarea>
                            <span class="error"><?= $errors['message'] ?? '' ?></span>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg">Submit Message</button>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </section>
    
    <?php require "footer.php"; ?>
</body>
</html>