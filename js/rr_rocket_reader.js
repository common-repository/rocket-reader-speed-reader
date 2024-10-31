/********************************************************************************************
 *
 *	ROCKET READER JQUERY PLUGIN
 *
 *	Revision: 01/28/2017
 *
 ********************************************************************************************/

// WORDS PER MINUTE FROM OPTIONS (WPM)
var wpm           = rr_init_WPM;
var delay_ms      = parseInt(60000 / wpm);	// DELAY BETWEEN WORDS (MILLISECONDS)
var playing       = false;
var words;
var word_ptr      = -1;
var currentPostID = 0;
var use_popup     = rr_init_use_popup;

jQuery( document ).ready(function() {
	var letter_width  = 20;
	
	// PUSH IN THE ROCKET READER HTML
	jQuery(".rr_wrapper, .rr_wrapper_popup").each(function() {
		var postid = jQuery(this).attr("postid");
		var rrhtml = '';
	
		rrhtml += '<div id="rr_reading_pane'+postid+'" class="rr_reading_pane">';
		rrhtml += '  <div id="rr_word_wrapper'+postid+'" class="rr_word_wrapper">';
		rrhtml += '    <div id="rr_word'+postid+'" class="rr_word"></div>';
		rrhtml += '  </div>';
		rrhtml += '</div>';
		rrhtml += '<div id="rr_button_container'+postid+'">';

		if(use_popup == 'Y') {
			rrhtml += '<script type="text/javascript">';
			rrhtml += 'jQuery(function() {';
			rrhtml += '	jQuery("#rr_wrapper'+postid+'").dialog({';
			rrhtml += '		autoOpen: false,';
			rrhtml += '     modal: true,';
			// HIDE CLOSE BUTTON
			rrhtml += '		dialogClass: "dlg-no-close",';
			// DISABLE ESC BUTTON
			rrhtml += '     closeOnEscape: false';
			rrhtml += '	});';
			
			rrhtml += 'jQuery("#rr_btn_play'+postid+'").on("click", function() {';
			rrhtml += '		jQuery("#rr_wrapper'+postid+'").dialog("open");';
			// ADJUST THE DYNAMIC POSITION
			rrhtml += '     jQuery("#rr_wrapper'+postid+'").css("margin-left","-"+(jQuery("#rr_wrapper'+postid+'").width()/2)+"px");';
			rrhtml += '     jQuery("#rr_wrapper'+postid+'").css("margin-top","-"+(jQuery("#rr_wrapper'+postid+'").height()/2)+"px");';		
	
			rrhtml += '     jQuery("#rr_wrapper'+postid+'").show();';
			rrhtml += '     rr_play("'+postid+'");';
			rrhtml += '	});';
			rrhtml += '});';
			rrhtml += '</script>';
		} else {
			rrhtml += '<div id="rr_btn_play'+postid+'" class="rr_btn_play">';
			rrhtml += '  <button onclick="rr_play('+postid+');" title="'+rr_read_with_reader+'">ROCKET READER</button>';
			rrhtml += '</div>';			
		} // if(use_popup == 'Y')
		
		rrhtml += '  <div id="rr_playing_controls'+postid+'" class="rr_playing_controls">';
		rrhtml += '    <div id="rr_btn_close'+postid+'">';
        rrhtml += '      <button class="rr_button" onclick="rr_close();" title="'+rr_close+'">&times;</button>';
		rrhtml += '    </div>';
		rrhtml += '    <div id="rr_btn_pause'+postid+'">';
        rrhtml += '      <button class="rr_button" onclick="rr_pause();" title="'+rr_pause+'">||</button>';
      	rrhtml += '    </div>';
		rrhtml += '    <div id="rr_btn_resume'+postid+'">';
        rrhtml += '      <button class="rr_button" onclick="rr_resume('+postid+');" title="'+rr_resume+'">&gt;</button>';
      	rrhtml += '    </div>';
		rrhtml += '    <div id="rr_btn_plus'+postid+'">';
        rrhtml += '      <button class="rr_button" onclick="rr_plus();" title="'+rr_increase_speed+'">+</button>';
      	rrhtml += '    </div>';
		rrhtml += '    <div id="rr_btn_minus'+postid+'">';
        rrhtml += '      <button class="rr_button" onclick="rr_minus();" title="'+rr_decrease_speed+'">-</button>';
      	rrhtml += '    </div>';
      	rrhtml += '    <div id="rr_btn_bold'+postid+'">';
        rrhtml += '      <button class="rr_button" onclick="rr_font_weight();" title="'+rr_bold_on_off+'">b</button>';
      	rrhtml += '    </div>';
		rrhtml += '  </div><!-- rr_playing_controls -->';
  		rrhtml += '</div><!-- rr_button_container -->';
  		rrhtml += '<div id="rr_delay'+postid+'" class="rr_delay"></div>';
  		rrhtml += '<br clear="all">';
		
		jQuery(this).html(rrhtml);
	});

	// VALUE FROM THE COOKIE OVERRIDES THE DEFAULT VALUE
	var tmp = getCookie("rr_wpm");
	if(typeof tmp != "undefined" && tmp != '' && tmp != 'NaN') wpm = parseInt(tmp);
	delay_ms = 60000 / wpm;
	
	// SET FONT WEIGHT, BASED ON A COOKIE
	var tmp = getCookie("rr_font_weight");
	var fw  = 400;
	if(typeof tmp != "undefined" && tmp != '' && tmp != 'NaN') fw = parseInt(tmp);
	jQuery(".rr_reading_pane").css("font-weight", fw);

	// DISPLAY PLAY BUTTONS WHEN PAGE IS FULLY LOADED
	jQuery(".rr_btn_play").show();
	

	/****************************************************************************************
	 *
	 *	PLAY BUTTON PRESSED: START THE READER
	 *
	 ****************************************************************************************/
	rr_play = function(postID) {
		if(playing)
			// OTTHER CONTROL PLAYING: CLOSE IT FAST AND QUEUE THIS ONE
			rr_close(postID);
		else {
			// READY TO PLAY
			currentPostID = postID;
			if(use_popup == 'N') {
				jQuery("#rr_btn_play"+currentPostID).hide();
				// v1.6.2
				jQuery("#rr_wrapper"+currentPostID).css("height", "158px");
			}
			jQuery("#rr_credits"+currentPostID).show();
			jQuery("#rr_btn_resume"+currentPostID).hide();
			jQuery("#rr_playing_controls"+currentPostID).show();			
			jQuery("#rr_delay"+currentPostID).show();
			jQuery("#rr_wrapper"+currentPostID).css("background-color", rr_init_cont_bgcolor);
			jQuery("#rr_wrapper"+currentPostID).css("border", "solid 1px "+rr_init_cont_bordercolor);		
			rr_show_speed();
			words = eval("words"+currentPostID);
			jQuery("#rr_reading_pane"+currentPostID).fadeIn(1500,
				function() {
					// CALLED WHEN THE FADE IS DONE
					playing = true;
					word_ptr = -1;
					rr_display_word();
				}
			);
		} // if(playing)
	} // rr_play()


	/****************************************************************************************
	 *
	 *	STOP BUTTON PRESSED: STOP THE READER
	 *
	 ****************************************************************************************/	
	rr_close = function(nextPostID, fadeTime) {
		if(typeof nextPostID != 'undefined' && nextPostID != null) {
			// QUICK CLOSE DOWN AND STARTING THE NEXT CLICKED CONTROL
			rr_final_close(nextPostID);
		} else {
			// FANCY CLOSE DOWN
			jQuery("#rr_wrapper"+currentPostID).fadeOut(fadeTime, 
				function() {
					// CALLED WHEN THE FADE IS DONE
					rr_final_close(nextPostID);;
				}
			);
		}
	} // rr_close()


	/****************************************************************************************
	 *
	 *	CLOSE THE READER (AFTER DELAY)
	 *
	 ****************************************************************************************/
	rr_final_close = function(nextPostID) {
		playing  = false;
		word_ptr = -1;
		jQuery("#rr_word"+currentPostID).html("");
		if(use_popup=='Y')
			jQuery("#rr_wrapper"+currentPostID).hide();
		else {
			jQuery("#rr_wrapper"+currentPostID).css("height","");
			jQuery("#rr_wrapper"+currentPostID).show();
			jQuery("#rr_btn_play"+currentPostID).show();
		}
		jQuery("#rr_credits"+currentPostID).hide();
		jQuery("#rr_playing_controls"+currentPostID).hide();	
		jQuery("#rr_delay"+currentPostID).hide();
		jQuery("#rr_reading_pane"+currentPostID).hide();
		jQuery("#rr_wrapper"+currentPostID).css("background-color","");
		jQuery("#rr_wrapper"+currentPostID).css("border","");
		if(typeof nextPostID != 'undefined' && nextPostID != null) rr_play(nextPostID);		
	} // rr_final_close()


	/****************************************************************************************
	 *
	 *	PAUSE BUTTON PRESSED: PAUSE THE READER
	 *
	 ****************************************************************************************/	
	rr_pause = function() {
		playing = false;
		jQuery("#rr_btn_resume"+currentPostID).show();
		jQuery("#rr_btn_pause"+currentPostID).hide();
	} // rr_pause()


	/****************************************************************************************
	 *
	 *	RESUME BUTTON PRESSED: RESUME THE READER
	 *
	 ****************************************************************************************/	
	rr_resume = function() {
		jQuery("#rr_btn_resume"+currentPostID).hide();
		jQuery("#rr_btn_pause"+currentPostID).show();
		playing = true;
		rr_display_word();
	} // rr_resume()


	/****************************************************************************************
	 *
	 *	MINUS BUTTON PRESSED: REDUCE THE SPEED
	 *
	 ****************************************************************************************/	
	rr_minus = function() {
		if(wpm>20) {
			wpm -= 10;
			document.cookie="rr_wpm="+wpm+"; expires=Thu, 5 Dec 2999 12:00:00 GMT; path=/";
			delay_ms = parseInt(60000 / wpm);
			rr_show_speed();
		}		
	} // rr_minus()
	

	/****************************************************************************************
	 *
	 *	PLUS BUTTON PRESSED: INCREASE THE SPEED
	 *
	 ****************************************************************************************/	
	rr_plus = function() {
		wpm += 10;
		document.cookie="rr_wpm="+wpm+"; expires=Thu, 5 Dec 2999 12:00:00 GMT; path=/";
		delay_ms = parseInt(60000 / wpm);
		rr_show_speed();		
	} // rr_plus()
	
	
	/****************************************************************************************
	 *
	 *	BOLD BUTTON PRESSED: TOGGLE NORMAL / BOLD
	 *
	 ****************************************************************************************/	
	rr_font_weight = function() {
		var fw = jQuery(".rr_reading_pane").css("font-weight");
		if(fw == 400) fw = 700; else fw = 400;
		document.cookie="rr_font_weight="+fw+"; expires=Thu, 5 Dec 2999 12:00:00 GMT; path=/";
		jQuery(".rr_reading_pane").css("font-weight", fw);
	} // rr_font_weight()
	

	/****************************************************************************************
	 *
	 *	DISPLAY THE CURRENT SPEED
	 *
	 ****************************************************************************************/		
	rr_show_speed = function() {
		jQuery("#rr_delay"+currentPostID).html(""+rr_speed+": <strong>"+wpm+"</strong> "+rr_words+" / "+rr_minute);
	} // rr_show_speed


	/****************************************************************************************
	 *
	 *	DISPLAY THE NEXT WORD
	 *
	 ****************************************************************************************/		
	rr_display_word = function() {
		
		if(!playing) return;

		if(word_ptr++>=words.length-1) {
			// LAST WORD DISPLAYED
			playing = false;
			// CLOSE READER (2000 = FADING TIME IN MS)
			rr_close(null, 2000);
			return;
		} // if(word_ptr++>=words.length-1)
		
		// NEXT WORD TO DISPLAY
		var wrd = words[word_ptr];
		
		if(wrd) {
			// WORD IS NOT EMPTY
			var orp = parseInt(wrd.length / 2);
			
			// PREVIOUS CHAR FOR EVEN LENGTH WORDS
			if(wrd.length % 2 == 0) orp--;
			
			// FIND CLOSEST VOWEL TO THE LEFT
			orp = rr_find_vowel(wrd, orp);
			
			// COLOR THE ORP LETTER RED
			wrd = rr_mark_orp(wrd, orp);

			// SET BACKGROUND COLOR, BASED ON THE OPTION IN THE DATABASE
			jQuery("#rr_reading_pane"+currentPostID).css("background-color", rr_init_bgcolor);
			jQuery("#rr_reading_pane"+currentPostID).css("border-color", rr_init_bordercolor);			
			
			// CALCULATE THE LEFT POS OF THE WORD BASED ON THE ORP
			var widthRP  = jQuery("#rr_reading_pane"+currentPostID).width()/2;
			var leftpos  = widthRP - orp * letter_width;
			
			// ADJUST THE LEFT POS OF THE DIV
			jQuery("#rr_word"+currentPostID).css("left", leftpos+"px");
			
			// SET THE TEXT COLOR, BASED ON THE OPTION IN THE DATABASE
			jQuery("#rr_word"+currentPostID).css("color", rr_init_textcolor);
			
			// STUFF THE WORD IN
			jQuery("#rr_word"+currentPostID).html(wrd);
		} // if(wrd)
		
		// SET THE TIMER FOR THE NEXT WORD
		if(word_ptr>=words.length-1)
		    // LAST WORD: INCREASE THE DELAY
			setTimeout(rr_display_word, 500);
		else
			setTimeout(rr_display_word, delay_ms);
			
	} // rr_display_word()


	/****************************************************************************************
	 *
	 *	MARK THE ORP (OPTICAL READING POINT / FOCUS POINT) IN A WORD
	 *
	 ****************************************************************************************/
	rr_mark_orp = function (wrd, orp) {
		// COLOR THE ORP LETTER RED
		return wrd.substr(0, orp)+'<span style="color:'+rr_init_fp_color+'">'+wrd.substr(orp,1)+"</span>"+wrd.substr(orp+1); 
	} // rr_mark_orp()


	/****************************************************************************************
	 *
	 *	FIND THE FIRST VOWEL TO THE LEFT
	 *
	 ****************************************************************************************/	
	rr_find_vowel = function (wrd, orp) {
		// FIND THE FIRST VOWEL TO THE LEFT
		for(i=orp; i>=0; i--) if (/[aeiou]/i.test(wrd[i])) return i;
		
		// NO VOWELS FOUND TO THE LEFT OF THE CENTER; USE ORIGINAL ORP (= LEFT CENTER POINT)
		return orp;
	} // rr_find_vowel()
});


/********************************************************************************************

	GET A COOKIE

*********************************************************************************************/
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
} // getCookie()
