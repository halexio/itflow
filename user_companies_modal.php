<div class="modal" id="editUserCompaniesModal<?php echo $user_id ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark">
      <div class="modal-header text-white">
        <h5 class="modal-title"><i class="fa fa-fw fa-building mr-2"></i><?php echo $name; ?> Companies</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="post.php" method="post" autocomplete="off">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="companies[]" value="<?php echo $permission_default_company; ?>">

        <div class="modal-body bg-white">

          <div class="alert alert-info">
            Select Companies that the user will need access to
          </div>

          <ul class="list-group">

            <?php
            $sql_companies_select = mysqli_query($mysqli,"SELECT * FROM companies ORDER BY company_name ASC");

            while($row = mysqli_fetch_array($sql_companies_select)){
              $company_id_select = $row['company_id'];
              $company_name_select = $row['company_name'];

            ?>
              <li class="list-group-item">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="companies[]" value="<?php echo $company_id_select; ?>" <?php if(in_array("$company_id_select",$permission_companies_array)){ echo "checked"; } ?> <?php if($permission_default_company == $company_id_select){ echo "disabled"; } ?>>
                  <label class="form-check-label ml-2"><?php echo $company_name_select; ?> <?php if($permission_default_company == $company_id_select){ echo "<small>(Default Company)</small>"; } ?></label>
                </div>
              </li>

            <?php
            }
            ?>

          </ul>

        </div>
        <div class="modal-footer bg-white">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_user_companies" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>