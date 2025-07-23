//SCRIPTS DE FUNCIONAMENTO DO SITE
$(document).ready(function() {

	
	//TOOLTIP
	$("[rel='tooltip']").tooltip();
	
	
	//TECLAS DE ATALHO
	$(document).keydown(function(e) {		
	
		//CADASTRAR REGISTRO COM A TECLA F2
		if (e.keyCode == 113) {			
			$("#cadastrar").trigger('click');
		}	
	
		//ATIVAR FOCO NO CAMPO DE BUSCA COM F8
		if (e.keyCode == 119) {			
			$("input[name='busca']").focus();
		}	
		
		//REMOVE OS REGISTROS SELECIONADOS AO PRESSIONAR A TECLA "DELETE"	
		if ($("input[name='busca']").is(":focus")) {
		}	else {
			if (e.keyCode == 46) {			
				$('#remover').submit();			
			}	
		}		
			
	});		
	
});


$(document).ready(function() {
 /**
 * jquery.mask.js
 * @author: Igor Escobar
 *
 * Created by Igor Escobar on 2012-03-10. Please report any bug at http://blog.igorescobar.com
 *
 * Copyright (c) 2012 Igor Escobar http://blog.igorescobar.com
 *
 * The MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

(function($) {
  "use strict";

  var  e, oValue, oNewValue, keyCode, pMask;

  var Mask = function(el, mask, options) {
    var plugin = this,
        $el = $(el),
        defaults = {
        byPassKeys: [8,9,37,38,39,40],
        specialChars: {':': 191, '-': 189, '.': 190, '(': 57, ')': 48, '/': 191, ',': 188, '_': 189, ' ': 32, '+': 187},
        translation: { 0: '(.)', 1: '(.)', 2: '(.)', 3: '(.)', 4: '(.)', 5: '(.)', 6: '(.)', 7: '(.)', 8: '(.)', 9: '(.)', 
                      'A': '(.)', 'S': '(.)',':': '(:)?', '-': '(-)?', '.': '(\\\.)?', '(': '(\\()?', ')': '(\\))?', '/': '(/)?', 
                      ',': '(,)?', '_': '(_)?', ' ': '(\\s)?', '+': '(\\\+)?'}};
        

    plugin.settings = {};
    plugin.init = function(){
      plugin.settings = $.extend({}, defaults, options);
        
      options = options || {};
      $el.each(function() {

        $el.attr('maxlength', mask.length);
        $el.attr('autocomplete', 'off');

        destroyEvents();
        setOnKeyUp();
        setOnPaste();    
      });
    };

    // public methods
    plugin.remove = function() {
      destroyEvents();
      $el.val(onlyNumbers($el.val()));
    };

    // private methods
    var onlyNumbers = function(string) {
      return string.replace(/\W/g, '');
    };

    var onPasteMethod = function(){
      setTimeout(function(){
        $el.trigger('keyup');
      }, 100);
    };

    var setOnPaste = function() {
      (hasOnSupport()) ? $el.on("paste", onPasteMethod) : $el.onpaste = onPasteMethod;
    };

    var setOnKeyUp = function(){
      $el.keyup(maskBehaviour).trigger('keyup');
    };

    var hasOnSupport = function() {
      return $.isFunction($.on);
    };

    var destroyEvents = function(){
      $el.unbind('keyup').unbind('onpaste');
    };

    var maskBehaviour = function(e){
      e = e || window.event;
      keyCode = e.keyCode || e.which;

      if ($.inArray(keyCode, plugin.settings.byPassKeys) >= 0) 
        return true;

      var oCleanedValue = onlyNumbers($el.val());

      pMask = (typeof options.reverse == "boolean" && options.reverse === true) ?
      getProportionalReverseMask(oCleanedValue, mask) :
      getProportionalMask(oCleanedValue, mask);

      oNewValue = applyMask(e, $el, pMask, options);

      if (oNewValue !== $el.val()){
        // workaround to trigger the change Event when setted
        $el.val(oNewValue).trigger('change');
      }
        
      return seekCallbacks(e, options, oNewValue, mask, $el);
    };

    var applyMask = function (e, fieldObject, mask, options) {

      oValue = onlyNumbers(fieldObject.val()).substring(0, onlyNumbers(mask).length);

      return oValue.replace(new RegExp(maskToRegex(mask)), function(){
        oNewValue = '';
        for (var i = 1; i < arguments.length - 2; i++) {
          if (typeof arguments[i] == "undefined" || arguments[i] === ""){
            arguments[i] = mask.charAt(i-1);
          }

          oNewValue += arguments[i];
        }

        return cleanBullShit(oNewValue, mask);
      });
    };

    var getProportionalMask = function (oValue, mask) {
      var endMask = 0, m = 0;

      while (m <= oValue.length-1){
        while(typeof plugin.settings.specialChars[mask.charAt(endMask)] === "number")
          endMask++;
        endMask++;
        m++;
      }

      return mask.substring(0, endMask);
    };

    var getProportionalReverseMask = function (oValue, mask) {
      var startMask = 0, endMask = 0, m = 0;
      startMask = (mask.length >= 1) ? mask.length : mask.length-1;
      endMask = startMask;

      while (m <= oValue.length-1) {
        while (typeof plugin.settings.specialChars[mask.charAt(endMask-1)] === "number")
          endMask--;
        endMask--;
        m++;
      }

      endMask = (mask.length >= 1) ? endMask : endMask-1;
      return mask.substring(startMask, endMask);
    };

    var maskToRegex = function (mask) {
      var regex = '';
      for (var i = 0; i < mask.length; i ++){
        if (plugin.settings.translation[mask.charAt(i)])
          regex += plugin.settings.translation[mask.charAt(i)];
      }
      return regex;
    };

    var validDigit = function (nowMask, nowDigit) {
      if (isNaN(parseInt(nowMask, 10)) === false && /\d/.test(nowDigit) === false) {
        return false;
      } else if (nowMask === 'A' && /[a-zA-Z0-9]/.test(nowDigit) === false) {
        return false;
      } else if (nowMask === 'S' && /[a-zA-Z]/.test(nowDigit) === false) {
        return false;
      } else if (typeof plugin.settings.specialChars[nowDigit] === "number" && nowMask !== nowDigit) {
        return false;
      }
      return true;
    };

    var cleanBullShit = function (oNewValue, mask) {
      oNewValue = oNewValue.split('');
      for(var i = 0; i < mask.length; i++){
        if(validDigit(mask.charAt(i), oNewValue[i]) === false)
          oNewValue[i] = '';
      }
      return oNewValue.join('');
    };

    var seekCallbacks = function (e, options, oNewValue, mask, currentField) {
      if (options.onKeyPress && e.isTrigger === undefined && typeof options.onKeyPress == "function") {
        options.onKeyPress(oNewValue, e, currentField, options);
      }

      if (options.onComplete && e.isTrigger === undefined &&
          oNewValue.length === mask.length && typeof options.onComplete == "function") {
        options.onComplete(oNewValue, e, currentField, options);
      }
    };

    plugin.init();
  };

  $.fn.mask = function(mask, options) {
    return this.each(function() {
      $(this).data('mask', new Mask(this, mask, options));
    });
  };

})(jQuery);

	//MÁSCARAS	
	$(".cpf").mask("000.000.000-00");
	$(".cnpj").mask("00.000.000/0000-00");
	$(".data").mask("00/00/0000");
	$(".cep").mask("00.000-000");
	$(".uf").mask("SS");
	$(".numero").mask("000000");
	$(function() {
		var mask_field = $('.contato'),
			options =  {onKeyPress: function(phone){
			if(/(\(11\)).+/i.test(phone))
			  mask_field.mask('(00) 00000-0000', options);
			else
			  mask_field.mask('(00) 0000-0000', options);         
		}};

		mask_field.mask('(00) 0000-0000', options);
	});	
	
});

/* FIM MÁSCARAS */

//PLACEHOLDER
/*!
* jQuery Placeholder Plugin v1.0
*
* code: https://github.com/uhtred/Placeholder
*
* Copyright (C) 2012 Daniel França(drfranca.com.br)
* Dual licensed under the MIT and GPL licenses:
* http://www.opensource.org/licenses/mit-license.php
* http://www.gnu.org/licenses/gpl.html
*
* Author: Daniel Ribeiro França
*/

;(function($, window, document, undefined) {

"use strict";

$.fn.placeholder = function() {

// Native placeholder feature detect
if ('placeholder' in document.createElement('input')) {
return this;
}

// Vars
var fields = this,
ph, self, hiddenPassword, passwordField;

// Main Object
ph = {

clearPlaceholders: function() {
fields.each(function() {
if ($.trim(this.value) === $(this).attr('placeholder')) {
this.value = '';
}
});
},
isEmpty: function(value) {
return $.trim(value) === '';
},
focusout: function() {
if (ph.isEmpty(this.value)) {

if ($(this).attr('type') === 'password') {
$(this).hide()
.next('input').show();
} else {

$(this).css('color', "#aaa");
this.value = $(this).attr('placeholder');

}
}
},
focusin: function() {
$(this).css('color', $(this).data('placeholder-old-color'));

if ($.trim(this.value) === $(this).attr('placeholder')) {
this.value = '';
}
},
focusPassword: function() {
passwordField = $(this).hide()
.prev('input').show();

//IE's hack
setTimeout(function() {
passwordField.trigger('focus');
}, 50);
},
preparePasswordPlaceholder: function(self) {
hiddenPassword = $($('<div>').append(self.clone()).html().replace(/type=["]?password["]?/, 'type="text"'))
.addClass('placeholder-password')
.removeAttr('id name')
.removeClass('placeholder');

self.hide().after(hiddenPassword);

return hiddenPassword;
},
preparePlaceholder: function(self) {

self.val(self.attr('placeholder'))
.data('placeholder-old-color', self.css('color'))
.css("color", "#aaa");

return self;
}

};

// Events
$(document.body)
.on('focusin.placeholder', '.placeholder', ph.focusin)
.on('focusout.placeholder', '.placeholder', ph.focusout)
.on('focus.placeholder', '.placeholder-password', ph.focusPassword)
.on('submit.placeholder', 'form', ph.clearPlaceholders);

// Init
return fields.each(function() {

self = $(this);

if (ph.isEmpty(this.value) || $.trim(this.value) === self.attr('placeholder')) {

self.addClass('placeholder');

if (self.attr('type') === 'password') {

self = ph.preparePasswordPlaceholder(self);
}

ph.preparePlaceholder(self);

}
});
};

})(jQuery, window, document);


//PLACEHOLDER
$(document).ready(function(){
$('input[placeholder], textarea[placeholder]').placeholder();
});