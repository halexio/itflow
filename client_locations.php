<?php 

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
  $sb = "location_name";
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

$sql = mysqli_query($mysqli,"SELECT SQL_CALC_FOUND_ROWS * FROM locations 
  WHERE location_client_id = $client_id 
  AND (location_name LIKE '%$q%' OR location_address LIKE '%$q%' OR location_phone LIKE '%$q%') 
  ORDER BY $sb $o LIMIT $record_from, $record_to");

$num_rows = mysqli_fetch_row(mysqli_query($mysqli,"SELECT FOUND_ROWS()"));

?>

<div class="card card-dark">
  <div class="card-header py-2">
    <h3 class="card-title mt-2"><i class="fa fa-fw fa-map-marker-alt"></i> Locations</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLocationModal"><i class="fas fa-fw fa-plus"></i> New Location</button>
    </div>
  </div>
  <div class="card-body">
    <form autocomplete="off">
      <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
      <input type="hidden" name="tab" value="<?php echo $_GET['tab']; ?>">
      <div class="row">
        
        <div class="col-md-4">
          <div class="input-group mb-3 mb-md-0">
            <input type="search" class="form-control" name="q" value="<?php if(isset($q)){echo stripslashes($q);} ?>" placeholder="Search <?php echo ucwords($_GET['tab']); ?>">
            <div class="input-group-append">
              <button class="btn btn-dark"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="float-right">
            <a href="post.php?export_client_<?php echo $_GET['tab']; ?>_csv=<?php echo $client_id; ?>" class="btn btn-default"><i class="fa fa-fw fa-download"></i> Export</a>
            <a href="#" class="btn btn-default"><i class="fa fa-fw fa-upload"></i> Import</a>
          </div>
        </div>

      </div>
    </form>
    <hr>
    <div class="table-responsive">
      <table class="table table-striped table-borderless table-hover">
        <thead class="<?php if($num_rows[0] == 0){ echo "d-none"; } ?>">
          <tr>
            <th><a class="text-secondary" href="?<?php echo $url_query_strings_sb; ?>&sb=location_name&o=<?php echo $disp; ?>">Name</a></th>
            <th><a class="text-secondary" href="?<?php echo $url_query_strings_sb; ?>&sb=location_address&o=<?php echo $disp; ?>">Address</a></th>
            <th><a class="text-secondary" href="?<?php echo $url_query_strings_sb; ?>&sb=location_phone&o=<?php echo $disp; ?>">Phone</a></th>
            <th><a class="text-secondary" href="?<?php echo $url_query_strings_sb; ?>&sb=location_hours&o=<?php echo $disp; ?>">Hours</a></th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
      
          while($row = mysqli_fetch_array($sql)){
            $location_id = $row['location_id'];
            $location_name = $row['location_name'];
            $location_country = $row['location_country'];
            $location_address = $row['location_address'];
            $location_city = $row['location_city'];
            $location_state = $row['location_state'];
            $location_zip = $row['location_zip'];
            $location_phone = formatPhoneNumber($row['location_phone']);
            if(empty($location_phone)){
              $location_phone_display = "-";
            }else{
              $location_phone_display = $location_phone;
            }
            $location_hours = $row['location_hours'];
            if(empty($location_hours)){
              $location_hours_display = "-";
            }else{
              $location_hours_display = $location_hours;
            }
            $location_photo = $row['location_photo'];
            $location_notes = $row['location_notes'];
            $location_primary = $row['location_primary'];
            $location_created_at = $row['location_created_at'];
            $location_contact_id = $row['location_contact_id'];
            if($location_id == $primary_location){
              $primary_location_display = "<p class='text-success'>Primary Location</p>";
            }else{
              $primary_location_display = "";
            }
      
          ?>
          <tr>
            <th>
              <i class="fa fa-fw fa-map-marker-alt text-secondary"></i> 
              <a class="text-dark" href="#" data-toggle="modal" data-target="#editLocationModal<?php echo $location_id; ?>"><?php echo $location_name; ?></a>
              <?php echo $primary_location_display; ?>
            </th>
            <td><a href="//maps.<?php echo $session_map_source; ?>.com?q=<?php echo "$location_address $location_zip"; ?>" target="_blank"><?php echo $location_address; ?><br><?php echo "$location_city $location_state $location_zip"; ?></a></td>
            <td><?php echo $location_phone_display; ?></td>
            <td><?php echo $location_hours_display; ?></td>
            <td>
              <div class="dropdown dropleft text-center">
                <button class="btn btn-secondary btn-sm" type="button" data-toggle="dropdown">
                  <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editLocationModal<?php echo $location_id; ?>">Edit</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="post.php?delete_location=<?php echo $location_id; ?>">Delete</a>
                </div>
              </div> 
              <?php include("edit_location_modal.php"); ?>     
            </td>
          </tr>

          <?php
          
          }
          
          ?>

        </tbody>
      </table>
    </div>
    <?php include("pagination.php"); ?>
  </div>
</div>

<?php include("add_location_modal.php"); ?>