<?php 
if(!isset($_SESSION))
{
    session_start();               
}

$action =isset($_GET['action'])?$_GET['action']:'cart';
$book = new Book();
$hd = new hoadonmodel();
$kh= new dangnhapmodel();
$cthd = new chitiethoadonmodel();

                use PHPMailer\PHPMailer\PHPMailer;
                use PHPMailer\PHPMailer\SMTP;
                use PHPMailer\PHPMailer\Exception;
if ($action=='cart')
{  
    $data = $book->sptrongcart();
    include './View/giohang.php';
}
if(!isset($_SESSION["cart"]))
    {
        $_SESSION["cart"] =array();           
    }
    if(isset($_GET['action']))
    {
        function update_cart($add = false)
        {
                    foreach ($_POST['quantity'] as $id => $quantity){
            if($quantity == 0){
                    unset($_SESSION['cart'][$id]);
            }else{
                if($add) {
                    $_SESSION['cart'][$id] += $quantity;
                }else{
                    $_SESSION['cart'][$id] = $quantity;
                }
            }
                    }
                }
        switch($_GET['action']){
            case "add":
                
                $tongtien = 0;
                update_cart(true);
               
                header('location: index.php?controller=cart&action=cart');

                break;

            case "delete":
                if(isset($_GET['id']))
                {
                    unset($_SESSION['cart'][$_GET['id']]);
                }
                header('location: index.php?controller=cart&action=cart');
                break;
            case "submit":
                if(isset($_SESSION["email"])){
                $tongtien = 0;
                $data=$book->sptrongcart();
                //var_dump($data);exit; 
                foreach($data as $value)
                {
                    $tongtien += $value['price']*$_SESSION['cart'][$value['book_id']]; 
                }

                $khachhang = $kh->khachhangcoemail($_SESSION["email"]);
                
                $email = $_SESSION["email"];            
                $order_id = "hd".rand(10,999);                     
                $consignee_name = $khachhang[0]['name'];   
                $consignee_add = $khachhang[0]['name'];      
                $diachi = $khachhang[0]['address'];
                $consignee_phone = $khachhang[0]['phone'];
                    
                $hdkh =$hd->insert($order_id, $email, $consignee_name, $consignee_add, $consignee_phone ,'1');
                foreach($data as $value)
                {
                    $ctdhngd = $cthd->insert($order_id, $value['book_id'], $_SESSION['cart'][$value['book_id']], $value['price']);
                }

                $stt=0;
                $content ="<table width='500' border = '1'>";
                $content .="<tr><th>STT</th><th>H??nh</th><th>S???n Ph???m</th><th>Gi?? Ti???n</th><th>S??? L?????ng</th><th>T???ng Ti???n</th></tr>";
                foreach($data as $value)
                {

                    $stt++;
                    $content .="<tr><td>".$stt.'</td><td><img src="https://bookstorestu.xyz/admin/back-end/View/assets/upload/'.$value['img'].'" style="width:50px;height:50px;"></td><td>'.$value['book_name']."</td><td>".$value['price']."</td><td>".$_SESSION['cart'][$value['book_id']]."</td><td>".$value['price']*$_SESSION['cart'][$value['book_id']]."</td></tr>";
                }
                $content .="</table>";
                unset($_SESSION['cart']);
                $message = "?????t h??ng th??nh c??ng.";
                echo "<script type='text/javascript'>alert('$message');</script> Nh???n v??o ????y ????? <a href='index.php'>Ti???p t???c mua h??ng</a>";
    require('mail/PHPMailer/Exception.php');
    require('mail/PHPMailer/SMTP.php');
    require('mail/PHPMailer/PHPMailer.php');

    $mail = new PHPMailer(true);

try {
    $mail->CharSet = "UTF-8";
    //Server settings
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'meomap2702@gmail.com';                     //SMTP username
    $mail->Password   = 'zpyubxyrdisbwfvv';                                //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('meomap2702@gmail.com', 'BOOK TORE STU');
    $mail->addAddress($email, $consignee_name);     //Add a recipient
    

    

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = '?????t h??ng th??nh c??ng';
    $mail->Body    = '<b>C??m ??n kh??ch h??ng ???? mua s???n ph???m</b><br><b>Email: </b>'.$email.'<br><b>M?? h??a ????n:</b>'."hd".rand(10,999).'<br><b>T??n ng?????i nh???n:</b>'.$khachhang[0]['name'].'<br><b>S??? ??i???n tho???i:</b>'.$khachhang[0]['phone'].'<br><b>?????a ch???:</b>'.$khachhang[0]['address'].'<br><b>Ti???n:</b>'.$tongtien;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

    break;

            }
    }else{
        $message = "B???n ch??a ????ng nh???p. Vui l??ng ????ng nh???p ????? ?????t h??ng!";
        echo "<script type='text/javascript'>alert('$message');</script> Nh???n v??o ????y ????? <a href='index.php?controller=dangnhap'>????ng nh???p</a><br>Ch??a c?? t??i kho???n Nh???n v??o ????y ????? <a href='index.php?controller=dangky'>????ng k??</br>";
        exit;
    }
   }
}

