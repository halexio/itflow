<div class="modal" id="editSoftwareModal<?php echo $software_id; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-fw fa-rocket"></i> <?php echo $software_name; ?></h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form action="post.php" method="post" autocomplete="off">
        <input type="hidden" name="software_id" value="<?php echo $software_id; ?>">
        <input type="hidden" name="login_id" value="<?php echo $login_id; ?>">
        <div class="modal-body bg-white">    
          
          <ul class="nav nav-pills nav-justified mb-3">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="pill" href="#pills-details<?php echo $software_id; ?>">Details</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="pill" href="#pills-notes<?php echo $software_id; ?>">Notes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="pill" href="#pills-login<?php echo $software_id; ?>">Login</a>
            </li>
          </ul>

          <hr>
          
          <div class="tab-content">

            <div class="tab-pane fade show active" id="pills-details<?php echo $software_id; ?>">

              <div class="form-group">
                <label>Software Name <strong class="text-danger">*</strong></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-fw fa-cube"></i></span>
                  </div>
                  <input type="text" class="form-control" name="name" placeholder="Software name" value="<?php echo $software_name; ?>" required>
                </div>
              </div>
              
              <div class="form-group">
                <label>Type <strong class="text-danger">*</strong></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-fw fa-tag"></i></span>
                  </div>
                  <select class="form-control select2" name="type" required>
                    <?php foreach($software_types_array as $software_type_select) { ?>
                    <option <?php if($software_type == $software_type_select) { echo "selected"; } ?>><?php echo $software_type_select; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            
              <div class="form-group">
                <label>License</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-fw fa-key"></i></span>
                  </div>
                  <input type="text" class="form-control" name="license" placeholder="License key" value="<?php echo $software_license; ?>" required> 
                </div>
              </div>

            </div>

            <div class="tab-pane fade" id="pills-notes<?php echo $software_id; ?>">
              
              <textarea class="form-control" rows="8" placeholder="Enter some notes" name="notes"><?php echo $software_notes; ?></textarea>

            </div>

            <div class="tab-pane fade" id="pills-login<?php echo $software_id; ?>">

              <div class="form-group">
                <label>Username</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-fw fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo $login_username; ?>">
                </div>
              </div>
              
              <div class="form-group">
                <label>Password</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-fw fa-lock"></i></span>
                  </div>
                  <input type="text" class="form-control" name="password" placeholder="Password" value="<?php echo $login_password; ?>">
                </div>
              </div>
            
            </div>

        </div>
        <div class="modal-footer bg-white">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_software" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>