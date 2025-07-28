<?php
    if(isset($_POST['submit'])){
        // Retrieve form information
        $firstname = $_POST['uname'];
        $email1 = $_POST['email'];
        $phone1 = $_POST['phone'];
        $message1 = $_POST['message'];
        
        //SET THE HEADER VARIABLES
        $headers = "From : $firstname" . "" . "$phone1" . "\r\n" .
                    "Email: $email1" . "\n\r" . "Message: $message1";
        if(mail("info@test.biruktransport.com", "Message From Website", $headers)){
            echo "Messege Sent Successfully!";
        }
        else{
            echo "Message Sent Failed";
        }
    }

?>


<html>
<head>
<title>HTML email</title>
</head>
<body>
<p>This email contains HTML Tags!</p>
<form action="" method="post">
    <input type="text" name="uname" placeholder="Your Name" >
    <input type="email" name="email" placeholder="Your Email" >
    <input type="number" placeholder="Phone" name="phone">
    <textarea id="id" cols="45" rows="15" name="message">
    </textarea>
    <button type="submit" name="submit">Submit</button>
</form>
</body>
</html>