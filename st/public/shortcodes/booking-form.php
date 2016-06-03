<?php if ($_POST) {
  $event = array(
    'post_type' => 'event',
    'post_title' => $title,
    'post_content' => $content,
    'post_status' => 'pending',
    'meta_input' => array(
      '_eventorganiser_schedule_start_start' => ,
      '_eventorganiser_schedule_start_finish' => ,
      '_eventorganiser_schedule_until' => ,
      '_eventorganiser_schedule_last_start' => ,
      '_eventorganiser_schedule_last_finish' => ,
    )
  );
}
?>
<form method="POST" enctype="multipart/form-data">
  <h2>General Event Information</h2>

  <label for="eventTitle">Event Title*</label><br/>
  <input type="text" id="eventTitle" name="libby[title]" required>
  <br /><br />
  <label for="eventDescription">Event Description*</label><br/>
   <?php wp_editor( '', 'description', array(
     'media_buttons' => false,
     'textarea_name' => 'libby[\'description\']',
     'textarea_rows' => 8
   ) ); ?>
  <br /><br />
  <label for="eventPhoto">Event Photo</label><br />
  <input type="file" id="eventPhoto" name="libby[event_photo]" />

  <br /><br />
  <label for="purpose">Purpose of Meeting*</label><br/>
  <textarea id="meetingPurpose" name="libby[purpose]"></textarea>
  <br /><br />
  <label for="attendance">Expected Attendance*</label><br/>
  <input type="text" id="attendance" name="libby[attendance]" required>


  <br /><br /><hr>

  <h2>Contact Information</h2>
  <label for="contactName">Contact Full Name*</label><br/>
  <input type="text" id="contactName" name="libby[contact_name]" required>
  <br /><br />
  <label for="contactPhone">Contact Phone*</label><br/>
  <input type="text" id="contactPhone" name="libby[contact_phone]" required>
  <br /><br />
  <label for="contactEmail">Contact Email*</label><br/>
  <input type="email" id="contactEmail" name="libby[contact_email]" required>

  <br /><br /><hr>

  <h2>Venue Information</h2>
  <label for="venue">Event Venue/Room</label><br />
  <select id="venue" name="libby[venue]" required>
    <option></option>
    <?php foreach ( $venues as $venue ): ?>
      <option value="<?php echo $venue->term_id; ?>"><?php echo $venue->name; ?></option>
    <?php endforeach; ?>
  </select>
  <div id="calendar" style="visibility:hidden;"></div>
  <div id="venueOptions" style="display:none;">
    <div id="venueDescription"></div>
    <h3>Available Setup Options</h3>
    <div id="venueSetup"></div>
    <h3>Available Equipment Options</h3>
    <div id="venueEquipment"></div>
  </div>
  <br /><br />

  <label for="actual_event_start_time_date">Actual Event Start Date</label>
  <input type="text" id="actual_event_start_time_date" /><br />
  <label for="actual_event_start_time_date">Actual Event Start Time</label>
  <input id="actual_event_start_time_time" disabled /><br />
  <input id="actual_event_end_time_date" disabled type="hidden" />
  <label for="actual_event_end_time_time">Actual Event End Time</label>
  <input id="actual_event_end_time_time" disabled />
  <br /><br />
  <label for="category">Event Category</label><br />
  <select id="category">
    <?php foreach ( $categories as $category ): ?>
      <option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
    <?php endforeach; ?>
  </select>
  <br /><br />

  <label for="groupType">Group Type</label><br />
  <select id="groupType">
    <?php foreach ( $group_types as $group_type ): ?>
      <option value="<?php echo $group_type->term_id; ?>"><?php echo $group_type->name; ?></option>
    <?php endforeach; ?>
  </select>
  <br /><br />

  <label for="message">Private Message to Calendar Editor</label><br/>
  <textarea id="message"></textarea>
  <br /><br />
  <input type="submit" />
</form>
