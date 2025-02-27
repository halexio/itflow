<?php include("config.php"); ?>
<?php include("functions.php"); ?>
<?php

require("vendor/PHPMailer-6.5.1/src/PHPMailer.php");
require("vendor/PHPMailer-6.5.1/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

?>

<?php

$sql_companies = mysqli_query($mysqli,"SELECT * FROM companies, settings WHERE companies.company_id = settings.company_id");

while($row = mysqli_fetch_array($sql_companies)){
  $company_id = $row['company_id'];
  $company_name = $row['company_name'];
  $company_phone = formatPhoneNumber($row['company_phone']);
  $company_email = $row['company_email'];
  $company_website = $row['company_website'];
  $config_enable_cron = $row['config_enable_cron'];
  $config_invoice_overdue_reminders = $row['config_invoice_overdue_reminders'];
  $config_invoice_prefix = $row['config_invoice_prefix'];
  $config_smtp_host = $row['config_smtp_host'];
  $config_smtp_username = $row['config_smtp_username'];
  $config_smtp_password = $row['config_smtp_password'];
  $config_smtp_port = $row['config_smtp_port'];
  $config_mail_from_email = $row['config_mail_from_email'];
  $config_mail_from_name = $row['config_mail_from_name'];
  $config_recurring_auto_send_invoice = $row['config_recurring_auto_send_invoice'];
  $config_enable_alert_low_balance = $row['config_enable_alert_low_balance'];
  $config_account_balance_threshold = $row['config_account_balance_threshold'];
  $config_base_url = $row['config_base_url'];

  if($config_enable_cron == 1){

    //GET ALERTS

    //DOMAINS EXPIRING 

    $domainAlertArray = [1,7,14,30,90,120];

    foreach($domainAlertArray as $day){

      //Get Domains Expiring
      $sql = mysqli_query($mysqli,"SELECT * FROM domains, clients 
        WHERE domain_client_id = client_id 
        AND domain_expire = CURDATE() + INTERVAL $day DAY
        AND domains.company_id = $company_id"
      );

      while($row = mysqli_fetch_array($sql)){
        $domain_id = $row['domain_id'];
        $domain_name = $row['domain_name'];
        $domain_expire = $row['domain_expire'];
        $client_id = $row['client_id'];
        $client_name = $row['client_name'];

        mysqli_query($mysqli,"INSERT INTO alerts SET alert_type = 'Domain', alert_message = 'Domain $domain_name for $client_name will expire in $day Days on $domain_expire', alert_date = NOW(), company_id = $company_id");

      }

    }

    //CERTIFICATES EXPIRING 

    $certificateAlertArray = [1,7,14,30,90,120];

    foreach($certificateAlertArray as $day){

      //Get Domains Expiring
      $sql = mysqli_query($mysqli,"SELECT * FROM certificates, clients 
        WHERE certificate_client_id = client_id 
        AND certificate_expire = CURDATE() + INTERVAL $day DAY
        AND certificates.company_id = $company_id"
      );

      while($row = mysqli_fetch_array($sql)){
        $certificate_id = $row['certificate_id'];
        $certificate_name = $row['certificate_name'];
        $certificate_domain = $row['certificate_domain'];
        $certificate_expire = $row['certificate_expire'];
        $client_id = $row['client_id'];
        $client_name = $row['client_name'];

        mysqli_query($mysqli,"INSERT INTO alerts SET alert_type = 'Certificate', alert_message = 'Certificate $certificate_name for $client_name will expire in $day Days on $certificate_expire', alert_date = NOW(), company_id = $company_id");

      }

    }

    //PAST DUE INVOICE ALERTS
    //$invoiceAlertArray = [$config_invoice_overdue_reminders];
    $invoiceAlertArray = [30,60,90,120,150,180,210,240,270,300,330,360,390,420,450,480,510,540,570,590,620];

    foreach($invoiceAlertArray as $day){

      $sql = mysqli_query($mysqli,"SELECT * FROM invoices
        LEFT JOIN clients ON invoice_client_id = client_id
        LEFT JOIN contacts ON contact_id = primary_contact
        WHERE invoice_status NOT LIKE 'Draft'
        AND invoice_status NOT LIKE 'Paid'
        AND invoice_status NOT LIKE 'Cancelled'
        AND DATE_ADD(invoice_due, INTERVAL $day DAY) = CURDATE()
        AND invoices.company_id = $company_id
        ORDER BY invoice_number DESC"
      );
            
      while($row = mysqli_fetch_array($sql)){
        $invoice_id = $row['invoice_id'];
        $invoice_prefix = $row['invoice_prefix'];
        $invoice_number = $row['invoice_number'];
        $invoice_status = $row['invoice_status'];
        $invoice_date = $row['invoice_date'];
        $invoice_due = $row['invoice_due'];
        $invoice_url_key = $row['invoice_url_key'];
        $invoice_amount = $row['invoice_amount'];
        $client_id = $row['client_id'];
        $client_name = $row['client_name'];
        $contact_name = $row['contact_name'];
        $contact_email = $row['contact_email'];

        mysqli_query($mysqli,"INSERT INTO alerts SET alert_type = 'Invoice', alert_message = 'Invoice $invoice_prefix$invoice_number for $client_name in the amount of $invoice_amount is overdue by $day days', alert_date = NOW(), company_id = $company_id");

        $mail = new PHPMailer(true);

        try{

          //Mail Server Settings

          $mail->SMTPDebug = 2;                                       // Enable verbose debug output
          $mail->isSMTP();                                            // Set mailer to use SMTP
          $mail->Host       = $config_smtp_host;  // Specify main and backup SMTP servers
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = $config_smtp_username;                     // SMTP username
          $mail->Password   = $config_smtp_password;                               // SMTP password
          $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
          $mail->Port       = $config_smtp_port;                                    // TCP port to connect to

          //Recipients
          $mail->setFrom($config_mail_from_email, $config_mail_from_name);
          $mail->addAddress("$contact_email", "$contact_name");     // Add a recipient

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML

          $mail->Subject = "Overdue Invoice $invoice_prefix$invoice_number";
          $mail->Body    = "Hello $contact_name,<br><br>According to our records, we have not received payment for invoice $invoice_prefix$invoice_number. Please submit your payment as soon as possible. If you have any questions please contact us at $company_phone.
            <br><br>
            Please view the details of the invoice below.<br><br>Invoice: $invoice_prefix$invoice_number<br>Issue Date: $invoice_date<br>Total: $$invoice_amount<br>Due Date: $invoice_due<br><br><br>To view your invoice online click <a href='https://$config_base_url/guest_view_invoice.php?invoice_id=$invoice_id&url_key=$invoice_url_key'>here</a><br><br><br>~<br>$company_name<br>$company_phone";
          
          $mail->send();

          mysqli_query($mysqli,"INSERT INTO history SET history_date = CURDATE(), history_status = 'Sent', history_description = 'Cron Emailed Overdue Invoice!', history_created_at = NOW(), history_invoice_id = $invoice_id, company_id = $company_id");

        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            mysqli_query($mysqli,"INSERT INTO history SET history_date = CURDATE(), history_status = 'Sent', history_description = 'Cron Failed to send Overdue Invoice!', history_created_at = NOW(), history_invoice_id = $new_invoice_id, company_id = $company_id");
        } //End Mail Try

      }

    }
    
    //LOW BALANCE ALERTS
    if($config_enable_alert_low_balance == 1){

      $sql = mysqli_query($mysqli,"SELECT * FROM accounts WHERE company_id = $company_id ORDER BY account_id DESC");

      while($row = mysqli_fetch_array($sql)){
        $account_id = $row['account_id'];
        $account_name = $row['account_name'];
        $opening_balance = $row['opening_balance'];

        $sql_payments = mysqli_query($mysqli,"SELECT SUM(payment_amount) AS total_payments FROM payments WHERE payment_account_id = $account_id");
        $row = mysqli_fetch_array($sql_payments);
        $total_payments = $row['total_payments'];

        $sql_revenues = mysqli_query($mysqli,"SELECT SUM(revenue_amount) AS total_revenues FROM revenues WHERE revenue_account_id = $account_id");
        $row = mysqli_fetch_array($sql_revenues);
        $total_revenues = $row['total_revenues'];
        
        $sql_expenses = mysqli_query($mysqli,"SELECT SUM(expense_amount) AS total_expenses FROM expenses WHERE expense_account_id = $account_id");
        $row = mysqli_fetch_array($sql_expenses);
        $total_expenses = $row['total_expenses'];

        $balance = $opening_balance + $total_payments + $total_revenues - $total_expenses;

        if($balance < $config_account_balance_threshold){
          mysqli_query($mysqli,"INSERT INTO alerts SET alert_type = 'Account Low Balance', alert_message = 'Threshold of $config_account_balance_threshold triggered low balance of $balance on account $account_name', alert_date = NOW(), company_id = $company_id");
        }

      }

    }

    //Send Recurring Invoices that match todays date and are active

    //Loop through all recurring that match today's date and is active
    $sql_recurring = mysqli_query($mysqli,"SELECT * FROM recurring, clients WHERE client_id = recurring_client_id AND recurring_next_date = CURDATE() AND recurring_status = 1 AND recurring.company_id = $company_id");

    while($row = mysqli_fetch_array($sql_recurring)){
      $recurring_id = $row['recurring_id'];
      $recurring_scope = $row['recurring_scope'];
      $recurring_frequency = $row['recurring_frequency'];
      $recurring_status = $row['recurring_status'];
      $recurring_last_sent = $row['recurring_last_sent'];
      $recurring_next_date = $row['recurring_next_date'];
      $recurring_amount = $row['recurring_amount'];
      $recurring_currency_code = $row['recurring_currency_code'];
      $recurring_note = mysqli_real_escape_string($mysqli,$row['recurring_note']); //Escape SQL
      $category_id = $row['recurring_category_id'];
      $client_id = $row['recurring_client_id'];
      $client_name = mysqli_real_escape_string($mysqli,$row['client_name']); //Escape SQL just in case a name is like Safran's etc
      $client_net_terms = $row['client_net_terms'];

      //Get the last Invoice Number and add 1 for the new invoice number
      $sql_invoice_number = mysqli_query($mysqli,"SELECT * FROM settings WHERE company_id = $company_id");
      $row = mysqli_fetch_array($sql_invoice_number);
      $config_invoice_next_number = $row['config_invoice_next_number'];
      
      $new_invoice_number = $config_invoice_next_number;
      $new_config_invoice_next_number = $config_invoice_next_number + 1;
      mysqli_query($mysqli,"UPDATE settings SET config_invoice_next_number = $new_config_invoice_next_number WHERE company_id = $company_id");

      //Generate a unique URL key for clients to access
      $url_key = keygen();

      mysqli_query($mysqli,"INSERT INTO invoices SET invoice_prefix = '$config_invoice_prefix', invoice_number = $new_invoice_number, invoice_scope = '$recurring_scope', invoice_date = CURDATE(), invoice_due = DATE_ADD(CURDATE(), INTERVAL $client_net_terms day), invoice_amount = '$recurring_amount', invoice_currency_code = '$recurring_currency_code', invoice_note = '$recurring_note', invoice_category_id = $category_id, invoice_status = 'Sent', invoice_url_key = '$url_key', invoice_created_at = NOW(), invoice_client_id = $client_id, company_id = $company_id");

      $new_invoice_id = mysqli_insert_id($mysqli);
      
      //Copy Items from original recurring invoice to new invoice
      $sql_invoice_items = mysqli_query($mysqli,"SELECT * FROM invoice_items WHERE item_recurring_id = $recurring_id ORDER BY item_id ASC");

      while($row = mysqli_fetch_array($sql_invoice_items)){
        $item_id = $row['item_id'];
        $item_name = mysqli_real_escape_string($mysqli,$row['item_name']); //SQL Escape incase of ,
        $item_description = mysqli_real_escape_string($mysqli,$row['item_description']); //SQL Escape incase of ,
        $item_quantity = $row['item_quantity'];
        $item_price = $row['item_price'];
        $item_subtotal = $row['item_subtotal'];
        $item_tax = $row['item_tax'];
        $item_total = $row['item_total'];
        $tax_id = $row['item_tax_id'];

        //Insert Items into New Invoice
        mysqli_query($mysqli,"INSERT INTO invoice_items SET item_name = '$item_name', item_description = '$item_description', item_quantity = '$item_quantity', item_price = '$item_price', item_subtotal = '$item_subtotal', item_tax = '$item_tax', item_total = '$item_total', item_created_at = NOW(), item_tax_id = $tax_id, item_invoice_id = $new_invoice_id, company_id = $company_id");
        
      }

      mysqli_query($mysqli,"INSERT INTO history SET history_date = CURDATE(), history_status = 'Sent', history_description = 'Invoice Generated from Recurring!', history_created_at = NOW(), history_invoice_id = $new_invoice_id, company_id = $company_id");

      mysqli_query($mysqli,"INSERT INTO alerts SET alert_type = 'Recurring', alert_message = 'Recurring Invoice $config_invoice_prefix$new_invoice_number for $client_name Sent', alert_date = NOW(), company_id = $company_id");

      //Update recurring dates

      mysqli_query($mysqli,"UPDATE recurring SET recurring_last_sent = CURDATE(), recurring_next_date = DATE_ADD(CURDATE(), INTERVAL 1 $recurring_frequency), recurring_updated_at = NOW() WHERE recurring_id = $recurring_id");

      if($config_recurring_auto_send_invoice == 1){
        $sql = mysqli_query($mysqli,"SELECT * FROM invoices
          LEFT JOIN clients ON invoice_client_id = client_id
          LEFT JOIN contacts ON contact_id = primary_contact
          WHERE invoice_id = $new_invoice_id
          AND invoices.company_id = $company_id"
        );

        $row = mysqli_fetch_array($sql);
        $invoice_prefix = $row['invoice_prefix'];
        $invoice_number = $row['invoice_number'];
        $invoice_date = $row['invoice_date'];
        $invoice_due = $row['invoice_due'];
        $invoice_amount = $row['invoice_amount'];
        $invoice_url_key = $row['invoice_url_key'];
        $client_id = $row['client_id'];
        $client_name = $row['client_name'];
        $contact_name = $row['contact_name'];
        $contact_email = $row['contact_email'];

        $mail = new PHPMailer(true);

        try{

          //Mail Server Settings

          //$mail->SMTPDebug = 2;                                       // Enable verbose debug output
          $mail->isSMTP();                                            // Set mailer to use SMTP
          $mail->Host       = $config_smtp_host;  // Specify main and backup SMTP servers
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = $config_smtp_username;                     // SMTP username
          $mail->Password   = $config_smtp_password;                               // SMTP password
          $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
          $mail->Port       = $config_smtp_port;                                    // TCP port to connect to

          //Recipients
          $mail->setFrom($config_mail_from_email, $config_mail_from_name);
          $mail->addAddress("$contact_email", "$contact_name");     // Add a recipient

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML

          $mail->Subject = "Invoice $invoice_prefix$invoice_number";
          $mail->Body    = "Hello $contact_name,<br><br>Please view the details of the invoice below.<br><br>Invoice: $invoice_prefix$invoice_number<br>Issue Date: $invoice_date<br>Total: $$invoice_amount<br>Due Date: $invoice_due<br><br><br>To view your invoice online click <a href='https://$config_base_url/guest_view_invoice.php?invoice_id=$new_invoice_id&url_key=$invoice_url_key'>here</a><br><br><br>~<br>$company_name<br>$company_phone";
          
          $mail->send();

          mysqli_query($mysqli,"INSERT INTO history SET history_date = CURDATE(), history_status = 'Sent', history_description = 'Cron Emailed Invoice!', history_created_at = NOW(), history_invoice_id = $new_invoice_id, company_id = $company_id");

          //Update Invoice Status to Sent
          mysqli_query($mysqli,"UPDATE invoices SET invoice_status = 'Sent', invoice_updated_at = NOW(), invoice_client_id = $client_id WHERE invoice_id = $new_invoice_id");

        }catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            mysqli_query($mysqli,"INSERT INTO history SET history_date = CURDATE(), history_status = 'Draft', history_description = 'Cron Failed to send Invoice!', history_created_at = NOW(), history_invoice_id = $new_invoice_id, company_id = $company_id");
        } //End Mail Try
      } //End if Autosend is on
    } //End Recurring Invoices Loop
    //Send Alert to inform Cron was run
    mysqli_query($mysqli,"INSERT INTO alerts SET alert_type = 'Cron', alert_message = 'Cron.php successfully executed', alert_date = NOW(), company_id = $company_id");
  } //End Cron Check
} //End Company Loop through

?>
