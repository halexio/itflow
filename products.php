<?php include("header.php");

//Paging
if(isset($_GET['p'])){
  $p = intval($_GET['p']);
  $record_from = (($p)-1)*$_SESSION['records_per_page'];
  $record_to = $_SESSION['records_per_page'];
}else{
  $record_from = 0;
  $record_to = $_SESSION['records_per_page'];
  $p = 1;
}
  
if(isset($_GET['q'])){
  $q = mysqli_real_escape_string($mysqli,$_GET['q']);
}else{
  $q = "";
}

if(!empty($_GET['sb'])){
  $sb = mysqli_real_escape_string($mysqli,$_GET['sb']);
}else{
  $sb = "product_name";
}

if(isset($_GET['o'])){
  if($_GET['o'] == 'ASC'){
    $o = "ASC";
    $disp = "DESC";
  }else{
    $o = "DESC";
    $disp = "ASC";
  }
}else{
  $o = "ASC";
  $disp = "DESC";
}

//Rebuild URL
$url_query_strings_sb = http_build_query(array_merge($_GET,array('sb' => $sb, 'o' => $o)));

$sql = mysqli_query($mysqli,"SELECT SQL_CALC_FOUND_ROWS * FROM products LEFT JOIN categories ON product_category_id = category_id 
  WHERE products.company_id = $session_company_id 
  AND (product_name LIKE '%$q%' OR product_description LIKE '%$q%' OR category_name LIKE '%$q%' OR product_cost LIKE '%$q%') 
  ORDER BY $sb $o LIMIT $record_from, $record_to");

$num_rows = mysqli_fetch_row(mysqli_query($mysqli,"SELECT FOUND_ROWS()"));

?>

<div class="card card-dark">
  <div class="card-header py-2">
    <h3 class="card-title mt-2"><i class="fa fa-fw fa-box"></i> Products</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal"><i class="fas fa-fw fa-plus"></i> New Product</button>
    </div>
  </div>

  <div class="card-body">
    <form class="mb-4" autocomplete="off">
      <div class="row">
        <div class="col-sm-4">
          <div class="input-group">
            <input type="search" class="form-control" name="q" value="<?php if(isset($q)){echo stripslashes($q);} ?>" placeholder="Search Products">
            <div class="input-group-append">
              <button class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </div>
      </div>
    </form>
    <hr>
    <div class="table-responsive">
      <table class="table table-striped table-borderless table-hover">
        <thead class="text-dark <?php if($num_rows[0] == 0){ echo "d-none"; } ?>">
          <tr>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=product_name&o=<?php echo $disp; ?>">Name</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=category_name&o=<?php echo $disp; ?>">Category</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=product_description&o=<?php echo $disp; ?>">Description</a></th>
            <th class="text-right"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=product_cost&o=<?php echo $disp; ?>">Cost</a></th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
      
          while($row = mysqli_fetch_array($sql)){
            $product_id = $row['product_id'];
            $product_name = $row['product_name'];
            $product_description = $row['product_description'];
            if(empty($product_description)){
              $product_description_display = "-";
            }else{
              $product_description_display = $product_description;
            }
            $product_cost = $row['product_cost'];
            $product_created_at = $row['product_created_at'];
            $category_id = $row['category_id'];
            $category_name = $row['category_name'];
            $product_tax_id = $row['product_tax_id'];

          ?>
          <tr>
            <td><a class="text-dark" href="#" data-toggle="modal" data-target="#editProductModal<?php echo $product_id; ?>"><?php echo $product_name; ?></a></td>
            <td><?php echo $category_name; ?></td>
            <td><?php echo $product_description_display; ?></td>
            <td class="text-right">$<?php echo number_format($product_cost,2); ?></td>
            <td>
              <div class="dropdown dropleft text-center">
                <button class="btn btn-secondary btn-sm" type="button" data-toggle="dropdown">
                  <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editProductModal<?php echo $product_id; ?>">Edit</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="post.php?delete_product=<?php echo $product_id; ?>">Delete</a>
                </div>
              </div>      
            </td>   
          </tr>

          <?php

          include("edit_product_modal.php");
          
          }
          
          ?>

        </tbody>
      </table>
    </div>
    <?php include("pagination.php"); ?>
  </div>
</div>

<?php 
  
  include("add_product_modal.php"); 
  include("add_quick_modal.php");

  include("footer.php");

?>