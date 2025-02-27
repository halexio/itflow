<?php include("header.php"); ?>

<?php

if(isset($_GET['year'])){
  $year = intval($_GET['year']);
}else{
  $year = date('Y');
}

//GET unique years from expenses, payments and revenues
$sql_all_years = mysqli_query($mysqli,"SELECT DISTINCT(YEAR(item_created_at)) AS all_years FROM invoice_items WHERE company_id = $session_company_id ORDER BY all_years DESC");

$sql_tax = mysqli_query($mysqli,"SELECT * FROM taxes WHERE company_id = $session_company_id ORDER BY tax_name ASC");

?>

<div class="card card-dark">
  <div class="card-header py-2">
    <h3 class="card-title mt-2"><i class="fa fa-fw fa-balance-scale"></i> Collected Tax Summary</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print"></i> Print</button>
    </div>
  </div>
  <div class="card-body p-0">
    <form class="p-3">
      <select onchange="this.form.submit()" class="form-control" name="year">
        <?php 
                
        while($row = mysqli_fetch_array($sql_all_years)){
          $all_years = $row['all_years'];
        ?>
        <option <?php if($year == $all_years){ echo "selected"; } ?> > <?php echo $all_years; ?></option>
        
        <?php
        }
        ?>

      </select>
    </form>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead class="text-dark">
          <tr>
            <th>Tax</th>
            <th class="text-right">Jan-Mar</th>
            <th class="text-right">Apr-Jun</th>
            <th class="text-right">Jul-Sep</th>
            <th class="text-right">Oct-Dec</th>
            <th class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while($row = mysqli_fetch_array($sql_tax)){
            $tax_id = $row['tax_id'];
            $tax_name = $row['tax_name'];
          ?>

            <tr>
              <td><?php echo $tax_name; ?></td>
              
              <?php

              $tax_collected_quarter_one = 0;
              
              for($month = 1; $month<=3; $month++) {
                
                $sql_tax_collected = mysqli_query($mysqli,"SELECT SUM(item_tax) AS tax_collected_for_month FROM taxes, invoices, invoice_items 
                  WHERE item_invoice_id = invoice_id
                  AND invoice_status LIKE 'Paid' 
                  AND item_tax_id = $tax_id 
                  AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                );
                
                $row = mysqli_fetch_array($sql_tax_collected);
                $tax_collected_for_month = $row['tax_collected_for_month'];
                
                $tax_collected_quarter_one = $tax_collected_quarter_one + $tax_collected_for_month;              
              }
              
              ?>
                
                <td class="text-right">$<?php echo number_format($tax_collected_quarter_one,2); ?></td>

              <?php

              $tax_collected_quarter_two = 0;
              
              for($month = 4; $month <= 6; $month ++) {
                
                $sql_tax_collected = mysqli_query($mysqli,"SELECT SUM(item_tax) AS tax_collected_for_month FROM taxes, invoices, invoice_items 
                  WHERE item_invoice_id = invoice_id
                  AND invoice_status LIKE 'Paid' 
                  AND item_tax_id = $tax_id 
                  AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                );
                
                $row = mysqli_fetch_array($sql_tax_collected);
                $tax_collected_for_month = $row['tax_collected_for_month'];
                
                $tax_collected_quarter_two = $tax_collected_quarter_two + $tax_collected_for_month;              
              }
              
              ?>
                
                <td class="text-right">$<?php echo number_format($tax_collected_quarter_two,2); ?></td>

              <?php

              $tax_collected_quarter_three = 0;
              
              for($month = 7; $month <= 9; $month ++) {
                
                $sql_tax_collected = mysqli_query($mysqli,"SELECT SUM(item_tax) AS tax_collected_for_month FROM taxes, invoices, invoice_items 
                  WHERE item_invoice_id = invoice_id
                  AND invoice_status LIKE 'Paid' 
                  AND item_tax_id = $tax_id 
                  AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                );
                
                $row = mysqli_fetch_array($sql_tax_collected);
                $tax_collected_for_month = $row['tax_collected_for_month'];
                
                $tax_collected_quarter_three = $tax_collected_quarter_three + $tax_collected_for_month;              
              }
              
              ?>
                
                <td class="text-right">$<?php echo number_format($tax_collected_quarter_three,2); ?></td>

              <?php

              $tax_collected_quarter_four = 0;
              
              for($month = 10; $month <= 12; $month ++) {
                
                $sql_tax_collected = mysqli_query($mysqli,"SELECT SUM(item_tax) AS tax_collected_for_month FROM taxes, invoices, invoice_items 
                  WHERE item_invoice_id = invoice_id
                  AND invoice_status LIKE 'Paid' 
                  AND item_tax_id = $tax_id 
                  AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                );
                
                $row = mysqli_fetch_array($sql_tax_collected);
                $tax_collected_for_month = $row['tax_collected_for_month'];

                $tax_collected_quarter_four = $tax_collected_quarter_four + $tax_collected_for_month;  
              }
              
              $total_tax_collected_four_quarters = $tax_collected_quarter_one + $tax_collected_quarter_two + $tax_collected_quarter_three + $tax_collected_quarter_four;

              ?>

              <td class="text-right">$<?php echo number_format($tax_collected_quarter_four,2); ?></td>        
              
              <td class="text-right">$<?php echo number_format($total_tax_collected_four_quarters,2); ?></td>
            </tr>
          
          <?php 

          } 
          
          ?>
          
          <tr>
            <th>Total Taxes<br><br><br></th>
            <?php
            
            $tax_total_for_quarter_one = 0;

            for($month = 1; $month<=3; $month++) {
              $sql_expenses = mysqli_query($mysqli,"SELECT SUM(expense_amount) AS expense_total_amount_for_month FROM expenses WHERE expense_category_id > 0 AND YEAR(expense_date) = $year AND MONTH(expense_date) = $month AND expense_vendor_id > 0 AND company_id = $session_company_id");
              $row = mysqli_fetch_array($sql_expenses);
              $expense_total_amount_for_quarter_one = $expense_total_amount_for_quarter_one + $row['expense_total_amount_for_month'];
            }
            
            ?>  
            
              <th class="text-right">$<?php echo number_format($expense_total_amount_for_quarter_one,2); ?></th>

            <?php

            $expense_total_amount_for_quarter_two = 0;
 
            for($month = 4; $month<=6; $month++) {
              $sql_expenses = mysqli_query($mysqli,"SELECT SUM(expense_amount) AS expense_total_amount_for_month FROM expenses WHERE expense_category_id > 0 AND YEAR(expense_date) = $year AND MONTH(expense_date) = $month AND expense_vendor_id > 0 AND company_id = $session_company_id");
              $row = mysqli_fetch_array($sql_expenses);
              $expense_total_amount_for_quarter_two = $expense_total_amount_for_quarter_two + $row['expense_total_amount_for_month'];
            }
            
            ?>  
            
              <th class="text-right">$<?php echo number_format($expense_total_amount_for_quarter_two,2); ?></th>

            <?php

            $expense_total_amount_for_quarter_three = 0;
 
            for($month = 7; $month<=9; $month++) {
              $sql_expenses = mysqli_query($mysqli,"SELECT SUM(expense_amount) AS expense_total_amount_for_month FROM expenses WHERE expense_category_id > 0 AND YEAR(expense_date) = $year AND MONTH(expense_date) = $month AND expense_vendor_id > 0 AND company_id = $session_company_id");
              $row = mysqli_fetch_array($sql_expenses);
              $expense_total_amount_for_quarter_three = $expense_total_amount_for_quarter_three + $row['expense_total_amount_for_month'];
            }
            
            ?>  
            
              <th class="text-right">$<?php echo number_format($expense_total_amount_for_quarter_three,2); ?></th>

            <?php

            $expense_total_amount_for_quarter_four = 0;
            
            for($month = 10; $month<=12; $month++) {
              $sql_expenses = mysqli_query($mysqli,"SELECT SUM(expense_amount) AS expense_total_amount_for_month FROM expenses WHERE expense_category_id > 0 AND YEAR(expense_date) = $year AND MONTH(expense_date) = $month AND expense_vendor_id > 0 AND company_id = $session_company_id");
              $row = mysqli_fetch_array($sql_expenses);
              $expense_total_amount_for_quarter_four = $expense_total_amount_for_quarter_four + $row['expense_total_amount_for_month'];
            }
            
            $total_expenses_for_all_four_quarters = $expense_total_amount_for_quarter_one + $expense_total_amount_for_quarter_two + $expense_total_amount_for_quarter_three + $expense_total_amount_for_quarter_four;

            ?>  
            
            <th class="text-right">$<?php echo number_format($expense_total_amount_for_quarter_four,2); ?></th>

            <th class="text-right">$<?php echo number_format($total_expenses_for_all_four_quarters,2); ?></th>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include("footer.php");