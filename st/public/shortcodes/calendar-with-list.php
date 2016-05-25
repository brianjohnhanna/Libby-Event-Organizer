<h3>Calendar</h3>
<style>
  #datepicker {
    width: 300px;
  }
  #datepicker .ui-datepicker,
  #datepicker .ui-datepicker table,
  #datepicker .ui-datepicker tr,
  #datepicker .ui-datepicker td,
  #datepicker .ui-datepicker th {
      margin: 0;
      padding: 0;
      border: none;
      border-spacing: 0;
  }

  #datepicker table thead th {
    border-width: 0 0 1px 0;
  }

  #datepicker table tbody td {
    border: 0;
  }

  #datepicker .ui-datepicker-header {
      position: relative;
      padding-bottom: 10px;
      border-bottom: 1px solid #d6d6d6;
  }
  #datepicker .ui-datepicker-title { text-align: center; }
  #datepicker .ui-datepicker-prev,
  #datepicker .ui-datepicker-next {
      position: absolute;
      top: -2px;
      padding: 5px;
      cursor: pointer;
  }

  #datepicker .ui-datepicker-prev {
      left: 0;
      padding-left: 0;
  }

  #datepicker .ui-datepicker-next {
      right: 0;
      padding-right: 0;
  }

  #datepicker .ui-datepicker-calendar td {
      padding: 0 7px;
      text-align: center;
      line-height: 26px;
  }

  #datepicker .ui-datepicker-calendar .ui-state-default {
      display: block;
      width: 26px;
      outline: none;
      text-decoration: none;
      color: #373737;
      border: 1px solid transparent;
      box-shadow: none;
  }
  #datepicker .ui-datepicker-calendar .ui-state-active {
    border: 1px solid #E5ECEF;
    background-color: #E5ECEF;
  }
</style>
<div id="datepicker"></div>
<ul id="miniCalEvents">
  <?php if ( isset( $todays_events ) && $todays_events ): ?>
    <?php foreach ( $todays_events as $event ) : ?>
      <li><?php echo $event->post_title; ?></li>
    <?php endforeach; ?>
  <?php else: ?>
    No events for today.
  <?php endif; ?>
</ul>

<script>
  (function($){
    $(document).ready(function(){
      var $eventsList = $('#miniCalEvents');
      var $datepicker = $('#datepicker').datepicker({
          inline: true,
          firstDay: 0,
          showOtherMonths: true,
          dayNamesMin: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
          onSelect: getEventsAjax,
          dateFormat: "yy-mm-dd",
          currentText: "Now"
      });
      function getEventsAjax(date) {
        var data = {
          'action': 'get_events_ajax',
          'date': date
        };
        $.get(ajaxurl, data, function(response){
          var events = JSON.parse(response);
          $eventsList.html('');
          if (events.length > 0) {
            for (var i=0; i < events.length; i++) {
              $eventsList.append('<li class="event">' + events[i]['post_title'] + '</li>');
            }
          }
          else {
            $eventsList.append('<li class="no-events">No events scheduled.</li>');
          }
        });
      }
    });
  })(jQuery);
</script>
