(function($){
	var s, calendar, venueId, venue, events, currentCalDate, dailyHours,
	BookingForm = {
		settings: {
			minDate: moment().add( 48, 'hours' ), // Tomorrow
			maxDate: moment().add( 3, 'months' ), // 3 months out
			dayStart: '10:00',
			dayEnd: '20:00',
			evtArray: [],
			openDays: []
		},
		elements: {
			eventTitle: $('input.eo-fes-form-element-type-event-title'),
			venueSelect: $('select.eo-event-form-select-venue'),
			venueOptions: $('#venueOptions'),
      venueDescription: $('#venueDescription'),
      venueSetupSection: $('#venueSetup'),
			venueSetupOptions: $('#venueSetup').find('div'),
      venueEquipmentSection: $('#venueEquipment'),
			venueEquipmentOptions: $('#venueEquipment').find('div'),
			calendar: $('#calendar'),
			eoDateTimePicker: $('#eo-fes-form-element-date-label').parent('.eo-fes-form-element '),
			startDate: $('#eo-event-start-date'),
			startTime: $('#eo-event-start-time'),
			endDate: $('#eo-event-end-date'),
			endTime: $('#eo-event-end-time'),
			clearEvent: $('#clearEvent'),
			eventDate: $('#eventDate'),
			calendarWrapper:  $('#calWrapper'),
			eventDateTable: $('#eventDateTable'),
			eventStartTable: $('#eventStartTable'),
			eventEndTable: $('#eventEndTable')
		},
		templates: {
			setupOption: wp.template('setup-option'),
			equipment: wp.template('equipment')
		},
		calendarArgs: {
			defaultView: 'agendaDay',
			header: {
				left: 'clearButton',
				center: 'title',
				right: 'prev,next'
			},
			customButtons: {
        clearButton: {
            text: 'Clear Selection',
            click: function() {
							BookingForm.removeEvent(s.evtArray, true)
						}
        }
    	},
			viewRender: function(currentView){
				if ( ! calendar ) {
					return;
				}
				// Past
				if (currentView.start.isBefore(s.minDate)) {
					$(".fc-prev-button").prop('disabled', true);
					$(".fc-prev-button").addClass('fc-state-disabled');
				}
				else {
					$(".fc-prev-button").removeClass('fc-state-disabled');
					$(".fc-prev-button").prop('disabled', false);
				}
				// Future
				if (currentView.end.isAfter(s.maxDate)) {
					$(".fc-next-button").prop('disabled', true);
					$(".fc-next-button").addClass('fc-state-disabled');
				} else {
					$(".fc-next-button").removeClass('fc-state-disabled');
					$(".fc-next-button").prop('disabled', false);
				}

				// Don't fire this stuff if it's the same day
				// so we don't get into an infinite loop
				if (currentView.start.isSame(currentCalDate, 'day')) {
					return;
				}

				// Update the current calendar date pointer var
				currentCalDate = currentView.start;
				// Get the hours for the current day
				var date = currentView.start.format('YYYY-MM-DD');
				BookingForm.getDailyHours(date);
			},
			allDaySlot: false,
			defaultDate: moment().add(3, 'days'),
			selectable: true,
			selectHelper: true,
			selectConstraint: "businessHours",
			slotDuration: '00:15:00',
			select: function(start, end) {

				if(start.isBefore(s.minDate)) {
					alert('All room requests must be made at least 48 hours in advance. Please select a later date.');
					$('#calendar').fullCalendar('unselect');
	        return false;
				}
				if(end.isAfter(s.maxDate)) {
					alert('All room requests must be made no more than 3 months out from the current date. Please select an earlier date.');
					$('#calendar').fullCalendar('unselect');
	        return false;
				}

				if(!BookingForm.checkIsValidTime(start, end)) {
					$('#calendar').fullCalendar('unselect');
					return false;
				}

				var eventData;
				eventData = {
					start: start,
					end: end,
					editable: true
				};
				var title = el.eventTitle.val();
				if (!title) {
					title = prompt('Please enter a title for your event.');
					el.eventTitle.val(title);
				}
				eventData.title = title;
				if (s.evtArray.length > 0){
					BookingForm.removeEvent(s.evtArray);
				}
				s.evtArray = calendar.fullCalendar('renderEvent', eventData, true);
				BookingForm.updateStartEndTime(start, end);
				calendar.fullCalendar('unselect');
			},
			eventDrop: function(event, delta, revertFunc) {
				if(!BookingForm.checkIsValidTime(event.start, event.end)) {
					revertFunc();
				}
				else {
					BookingForm.updateStartEndTime( event.start, event.end );
				}
			},
			eventResize: function(event, delta, revertFunc) {
				if(!BookingForm.checkIsValidTime(event.start, event.end)) {
					revertFunc();
				}
				else {
					BookingForm.updateStartEndTime( event.start, event.end );
				}
			},
			selectOverlap: false,
			eventOverlap: false,
			editable: false,
		},
		init: function() {
			s = this.settings;
			el = this.elements;
			tmpl = this.templates;
			this.initCalendar();
			this.initDatePicker();
			this.bindUIActions();
		},
		initCalendar() {
			calendar = el.calendar.fullCalendar(this.calendarArgs);
		},
		initDatePicker() {
			el.eventDate.datepicker({
				minDate: s.minDate.toDate(),
				maxDate: s.maxDate.toDate(),
				onSelect: function() {
					BookingForm.changeCalendarDate($(this).datepicker('getDate'));
				}
			});
		},
		bindUIActions: function() {
			el.clearEvent.on( 'click', function(e){
				BookingForm.removeEvent(s.evtArray, true);
			});
			el.venueSelect.on('change', function(e){
				// Set the venueId variable
				venueId = $(this).val();
				// Get the venue info via ajax
				BookingForm.getVenueAjax(venueId);
			});

			BookingForm.disableEventStartEndTime();
		},
		removeEvent: function(evtArray, clearSelection) {
			if (evtArray[0] !== undefined && evtArray[0].hasOwnProperty('_id')) {
				calendar.fullCalendar( 'removeEvents', evtArray[0]._id);
				evtArray = [];
			}
			if (clearSelection) {
				BookingForm.updateStartEndTime('','');
			}
		},
		updateStartEndTime: function(start, end) {
			el.startDate.val( start ? start.format('MM-DD-YYYY') : '' );
			el.startTime.val( start ? start.format('hh:mm A') : '' );
			el.endDate.val( end ? end.format('MM-DD-YYYY') : '' );
			el.endTime.val( end ? end.format('hh:mm A') : '' );
			el.eventDateTable.html( start ? start.format('MM-DD-YYYY') : ' ' );
			el.eventStartTable.html( start ? start.format('hh:mm A') : '' );
			el.eventEndTable.html( end ? end.format('hh:mm A') : '' );
		},
		getVenueAjax: function(venueId) {
			if (venueId > 0){
	      var data = {
	        action: 'get_venue_details_ajax',
	        venueId: venueId
	      };
	      $.getJSON(ajax_url, data, function(response){
					venue = response;

					BookingForm.setVenueInfo(response);

					// Get the hours for the current day
					currentCalDate = calendar.fullCalendar('getDate');
					BookingForm.getDailyHours(currentCalDate.format('YYYY-MM-DD'));

					// Show the venue options
					el.venueOptions.show();
					el.calendarWrapper.removeClass('hidden');
				});
			}
			else {
				el.venueOptions.hide();
				el.calendarWrapper.addClass('hidden');
			}
		},
		setVenueInfo: function(infoObject) {
			events = infoObject.events;
			this.setVenueDescription(infoObject.description);
			this.setVenueSetupOptions(infoObject.setup);
			this.setVenueEquipmentOptions(infoObject.equipment);
			this.updateEvents(infoObject.events);
		},
		setVenueDescription: function(description) {
			el.venueDescription.html('');
			if (description) {
        el.venueDescription.html(description);
      }
		},
		setVenueSetupOptions: function(setupOptions) {
			el.venueSetupOptions.html('');
			if (setupOptions.length > 0) {
        $.each(setupOptions, function(i,v){
          el.venueSetupOptions.append(tmpl.setupOption(v));
        });
				el.venueSetupSection.show();
      }
			else {
				el.venueSetupSection.hide();
			}
		},
		setVenueEquipmentOptions: function(equipmentOptions) {
			el.venueEquipmentOptions.html('');
			if (equipmentOptions.length > 0) {
        $.each(equipmentOptions, function(i,v){
          el.venueEquipmentOptions.append(tmpl.equipment(v));
				});
				el.venueEquipmentSection.show();
      }
			else {
				el.venueEquipmentSection.hide();
			}
		},
		updateEvents: function(events) {
			calendar.fullCalendar('removeEvents');
			calendar.fullCalendar('addEventSource', events);
      calendar.fullCalendar('rerenderEvents');
			this.removeEvent(s.evtArray, true);
		},
		changeCalendarDate: function(date) {
			calendar.fullCalendar('gotoDate', date);
		},
		getOpenTime: function(startObj) {
			var dayOfWeek = startObj.format('E');
			for (var i = 0; i < venue.hours.length; i++) {
				if (venue.hours[i].day_of_week == dayOfWeek && !venue.hours[i].closed) {
					return venue.hours[i].opening_hour.replace(/\D/g, '');
				}
			}
		},
		getClosingTime: function(endObj) {
			var dayOfWeek = endObj.format('E');
			for (var i = 0; i < venue.hours.length; i++) {
				if (venue.hours[i].day_of_week == dayOfWeek) {
					return venue.hours[i].closing_hour.replace(/\D/g, '');
				}
			}
		},
		checkIsValidTime: function(startObj, endObj) {
			if(dailyHours.closed) {
				alert('The library is closed on ' + startObj.format('dddd, MMMM Do') + '. Please select a different date.');
				return false;
			}
			var validStartHourObj = moment(dailyHours.open_time, 'hh:mm A').add(30, 'minutes');
			if(startObj.isBefore(startObj.clone().set({'hour': validStartHourObj.format("HH"), 'minute': validStartHourObj.format("mm")}))) {
				alert('You have selected in invalid time. Bookings must start 1 hour after the library opens. \n\nThe library opens at ' + dailyHours.open_time + ' on ' + startObj.format('dddd, MMMM Do'));
				return false;
			}
			var validEndHourObj = moment(dailyHours.close_time, 'hh:mm A').subtract(30, 'minutes');
			if(endObj.isAfter(endObj.clone().set({'hour': validEndHourObj.format("HH"), 'minute': validEndHourObj.format("mm")}))) {
				alert('You have selected in invalid time. Bookings must end one hour before the library closes.\n\nThe library closes at ' + dailyHours.close_time + ' on ' + endObj.format('dddd, MMMM Do'));
				return false;
			}
			return true;
		},
		disableEventStartEndTime: function() {
			el.eoDateTimePicker.addClass('hidden');
		},
		getDailyHours: function(date) {
			var data = {
				action: 'get_daily_hours_ajax',
				venueId: venueId,
				date: date
			};
			$.getJSON(ajax_url, data, function(response){
				BookingForm.setDailyHours(response);
			});
		},
		setDailyHours: function(hours) {
			if (!hours) return false;
			dailyHours = hours;
			//We have to destroy the calendar and recreate it with the new days of week
			var options = {
				businessHours: {},
				minTime: s.dayStart,
				maxTime: s.dayEnd,
				defaultDate: currentCalDate,
				eventSources: {
					events: events,
				}
			};
			if ( hours.closed ) {
				// Make it so if they are closed there is no day of week they are open
				options.businessHours.dow = [];
			}
			else {
				var dayStartObj = moment(hours.open_time, 'hh:mm A');
				var dayEndObj = moment(hours.close_time, 'hh:mm A');
				if (!dayStartObj.isValid() || !dayEndObj.isValid()) {
					console.error('Not a valid start or end time. Cannot properly render the business hours for the daily agenda view. Check that open_time and close_time are output in hh:mm A format.', hours);
					return;
				}
				options.minTime = dayStartObj.format('HH:mm');
				options.maxTime = dayEndObj.format('HH:mm');
				options.businessHours = {
					start: dayStartObj.clone().add(1, 'hour').format('HH:mm'),
					end: dayEndObj.clone().subtract(1, 'hour').format('HH:mm'),
					dow: [currentCalDate.format('d')] // Otherwise set it so there the day of week is the current date, hours take care of the rest
				};
			}
			var calendarArgs = $.extend(this.calendarArgs, options);
			calendar.fullCalendar('destroy');
			calendar = calendar.fullCalendar(calendarArgs);
		},
	};

	$(document).ready(function() {
		BookingForm.init();
	});

})(jQuery);
