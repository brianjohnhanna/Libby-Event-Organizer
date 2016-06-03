(function($){
	var s, calendar, venue,
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
			},
			allDaySlot: false,
			defaultDate: moment().add(3, 'days'),
			selectable: true,
			selectHelper: true,
			// selectConstraint: "businessHours",
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
					alert('You have selected in invalid time. Bookings must start 1 hour after the library opens and end one hour before the library closes.');
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
					alert('You have selected in invalid time. Bookings must start 1 hour after the library opens and end one hour before the library closes.');
					revertFunc();
				}
				else {
					BookingForm.updateStartEndTime( event.start, event.end );
				}
			},
			eventResize: function(event, delta, revertFunc) {
				if(!BookingForm.checkIsValidTime(event.start, event.end)) {
					alert('You have selected in invalid time. Bookings must start 1 hour after the library opens and end one hour before the library closes.');
					revertFunc();
				}
				else {
					BookingForm.updateStartEndTime( event.start, event.end );
				}
			},
			selectOverlap: false,
			eventOverlap: false,
			editable: false,
			// events: fcEvents,
			// resources: fcResources,
			schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
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
				BookingForm.getVenueAjax($(this).val());
			});

			//TODO: this event listener doesn't seem to work... Need to override the onSelect method
			el.startDate.on('change', function(e){
				console.log('changed');
				// BookingForm.changeCalendarDate($(this).datepicker('getDate'));
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
					console.log(response);
					venue = response;
	        BookingForm.setVenueInfo(response);
					// BookingForm.setOpenDays(response.hours);
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
			var dayOfWeek = startObj.format('E');
			var dayHours = venue.hours.filter(function(v){
				return v.day_of_week == dayOfWeek;
			});
			dayHours = dayHours[0];
			if(dayHours.closed) {
				return false;
			}
			var openHour = moment(dayHours.opening_hour, 'h:mm A').format("HH");
			if(startObj.isBefore(startObj.clone().set('hour', openHour).add(1, 'hours'))) {
				return false;
			}
			var closeHour = moment(dayHours.closing_hour, 'h:mm A').format("HH");
			if(endObj.isAfter(endObj.clone().set('hour', closeHour).subtract(2, 'hours'))) {
				return false;
			}
			return true;
		},
		setOpenDays: function(hours) {
			if (!hours) return false;
			var openDays = hours.filter(function(obj){
				return !obj.closed;
			});
			var daysOfWeek = openDays.map(function(obj){
				return parseInt(obj.day_of_week) === 7 ? parseInt(obj.day_of_week) - 7 : parseInt(obj.day_of_week);
			});
			calendar.fullCalendar('destroy');
			//We have to destroy the calendar and recreate it with the new days of week
			var options = {
				businessHours: {
					start: s.dayStart,
					end: s.dayEnd,
					dow: daysOfWeek
				},
				minTime: s.dayStart,
				maxTime: s.dayEnd,
			};
			var calendarArgs = $.extend(this.calendarArgs, options);
			calendar.fullCalendar(calendarArgs);
		},
		disableEventStartEndTime: function() {
			el.eoDateTimePicker.addClass('hidden');
		}
	};

	$(document).ready(function() {
		BookingForm.init();
	});

})(jQuery);
