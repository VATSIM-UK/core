<?php if ($regType == Enum_Account_Types::STUDENT): ?>
    <p>
        Thank you for choosing our student account!
    </p>
    <p>
        By signing up to our student account you'll be able to take advantage of
        these great benefits:
    </p>
    <ul>
        <li>Search for rooms or properties for yourself or a group of you.</li>
        <li>Contact landlords and agencies about properties.</li>
        <li>Post details of spare rooms you have in your house.</li>
        <li>Find information of other like-minded people</li>
    </ul>
<?php elseif ($regType == Enum_Account_Types::LANDLORD): ?>
    <p>
        Thank for your choosing our landlord account!
    </p>
    <p>
        By signing up to our landlord account you'll be able to take advantage of
        these great benefits:
    </p>
    <ul>
        <li>Advertise properties you own - either as full units, or individual rooms.</li>
        <li>Maintain an inventory of what's included with your properties.</li>
        <li>Receive messages from students looking to rent.</li>
    </ul>
<?php elseif ($regType == Enum_Account_Types::AGENT): ?>
    <p>
        Thank for your choosing our agency account!
    </p>
    <p>
        By signing up to our agency account package you'll be able to take advantage of
        these great benefits:
    </p>
    <ul>
        <li>Register your letting agency and grant multiple users access.</li>
        <li>Advertise all properties you're responsible for, either as a single unit or as individual rooms.</li>
        <li>Maintain an inventory of what's included with your properties.</li>
        <li>Receive messages from students looking to rent.</li>
        <li>Have access to an "automatic viewing booker" which takes the hassle out of organising viewings</li>
    </ul>
<?php elseif ($regType == Enum_Account_Types::BUSINESS): ?>
    <p>
        Thank for your choosing our business account!
    </p>
    <p>
        By signing up to our business account package you'll be able to take advantage of
        these great benefits:
    </p>
    <ul>
        <li>Local: Advertise your part-time student jobs available in the area.</li>
        <li>Local: Advertise graduate jobs available in the area.</li>
        <li>National: Advertise part-time student jobs available nationwide.</li>
        <li>National: Advertise graduate jobs available nationwide.</li>
        <li>Receive applications electronically, in a standard format containing specific details.</li>
        <li>Contact applicants directly in bulk or individually.</li>
        <li>Organise interview/meeting dates with applicants.</li>
    </ul>
<?php endif; ?>
<p>
    <a href="<?= URL::site("account/register") ?>">If you've selected the wrong account type and wish to change, click here.</a>
</p>

<form class="form-horizontal" action="<?= URL::site("account/register_details") ?>" method="POST">
    <legend>Personal Details</legend>
    <?php if ($regType == Enum_Account_Types::AGENT || $regType == Enum_Account_Types::BUSINESS): ?>
        <p>
            Before being able to create your company profile, we need to create an account for yourself.  Please enter
            your personal details below so that we can proceed with the account registration process.
        </p>
    <?php endif; ?>
    <div class="control-group <?=((isset($errors) && in_array("name_first", $errors)) ? "error" : "")?>">
        <label class="control-label" for="name_first">First Name</label>
        <div class="controls">
            <input type="text" name="name_first" pattern="\w*" value="<?=$request->post("name_first")?>" required>
            <?php if(isset($errors) && in_array("name_first", $errors)): ?>
                <span class="help-inline">Please enter your first name.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="control-group <?=((isset($errors) && in_array("name_last", $errors)) ? "error" : "")?>">
        <label class="control-label" for="name_last">Surname</label>
        <div class="controls">
            <input type="text" name="name_last" value="<?=$request->post("name_last")?>">
            <?php if(isset($errors) && in_array("name_last", $errors)): ?>
                <span class="help-inline">Please enter your last name.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="control-group <?=((isset($errors) && in_array("gender", $errors)) ? "error" : "")?>">
        <label class="control-label" for="gender">Gender</label>
        <div class="controls">
            <select name="gender" required>
                <option value="">Please select...</option>
                <option value="M" <?=(($request->post("gender") == "M") ? "selected='selected'" : "")?>>Male</option>
                <option value="F" <?=(($request->post("gender") == "F") ? "selected='selected'" : "")?>>Female</option>
            </select>
            <?php if(isset($errors) && in_array("gender", $errors)): ?>
                <span class="help-inline">Please select a gender.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="control-group <?=((isset($errors) && in_array("dob", $errors)) ? "error" : "")?>">
        <label class="control-label" for="dob">Date of Birth</label>
        <div class="controls">
            <input type="text" name="dob" value="<?=$request->post("dob")?>" placeholder="d d / m m / y y y y" required>
            <?php if(isset($errors) && in_array("dob", $errors)): ?>
                <span class="help-inline">Please enter a valid date of birth.</span>
            <?php endif; ?>
        </div>
    </div>
    <legend>Account and Contact Details</legend>
    <div class="control-group <?=((isset($errors) && in_array("model/accountemail.email.email_check_unique", $errors)) ? "error" : "")?>">
        <label class="control-label" for="email">Email</label>
        <div class="controls">
            <input type="email" name="email" value="<?=$request->post("email")?>" required>
            <?php if(isset($errors) && in_array("model/accountemail.email.email_check_unique", $errors)): ?>
                <span class="help-inline">Please enter a valid email address that hasn't previously been used.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="control-group <?=((isset($errors) && in_array("password", $errors)) ? "error" : "")?>">
        <label class="control-label" for="password">Desired Password</label>
        <div class="controls">
            <input type="password" name="password" value="<?=$request->post("password")?>" required>
            <?php if(isset($errors) && in_array("password", $errors)): ?>
                <span class="help-inline">Your passwords must be a minimum of 8 characters, contain 1 uppercase character and 1 number.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="control-group <?=((isset($errors) && in_array("password", $errors)) ? "error" : "")?>">
        <label class="control-label" for="password2">Confirm Password</label>
        <div class="controls">
            <input type="password" name="password2" value="<?=$request->post("password2")?>" required>
            <?php if(isset($errors) && in_array("password", $errors)): ?>
                <span class="help-inline">Please ensure both passwords match.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Account</button>
        <button type="button" class="btn">Cancel</button>
    </div>
</form>