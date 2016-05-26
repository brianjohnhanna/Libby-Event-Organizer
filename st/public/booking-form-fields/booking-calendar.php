<div id="calWrapper" class="hidden">
  <label for="eventDate">Go To Date</label>
  <input type="text" id="eventDate" /><br /><br />
  <div id="calendar"></div>
  <br /><br />
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td id="eventDateTable">&nbsp;</td>
        <td id="eventStartTable">&nbsp;</td>
        <td id="eventEndTable">&nbsp;</td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3">
          <em>
            <?php
              $timing_disclaimer = 'Please specify the time required INCLUDING setup and breakdown time so we know the total time you need the room for. This is not to exceed 4 hours unless you have requested special circumstances.';
              echo apply_filters( 'libby/events/form/timing_disclaimer', $timing_disclaimer );
            ?>
          </em>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
<noscript>Javascript is required to be enabled in order to use this booking form. Please enable Javascript or update your browser</noscript>
