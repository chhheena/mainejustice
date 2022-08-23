(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.tagging = factory( root, $ );

    // Also register as jQuery plugin.
    $.fn.jomsTagging = function( extraFetch ) {
        return this.each(function() {
            joms.util.tagging( this, extraFetch );
        });
    };

})( window, joms.jQuery, function( window, $ ) {

var

// Virtual keys.
VK_ENTER   = 13,
VK_ESC     = 27,
VK_KEYUP   = 38,
VK_KEYDOWN = 40,

// Namespace.
namespace = 'joms-tagging',

// CSS selectors.
cssTextareaS           = '.joms-textarea',
cssWrapper            = cssTextareaS + '__wrapper',
cssBeautifier         = cssTextareaS + '__beautifier',
cssHidden             = cssTextareaS + '__hidden',
cssDropdown           = cssTextareaS + '__tag-ct',
cssDropdownItem       = cssTextareaS + '__tag-item',
cssDropdownItemActive = cssDropdownItem + '--active',

// Regular expressions.
rTags           = /@\[\[(\d+):contact:([^\]]+)\]\]/g,
rTag            = /@\[\[(\d+):contact:([^\]]+)\]\]/,
rHashTag        = /(^|#|\s)(#[^#\s]+)/g,
rHashTagReplace = '$1<b>$2</b>',
rEol            = /\n/g,
rEolReplace     ='<br>';
var cssTextarea           = '.input.joms-textarea';
function Tagging( textarea, extraFetch ) {
    this.textarea = textarea;
    this.fetcher = extraFetch || false;
    this.$textarea = $( textarea );
    this.$textarea.data( 'initialValue', textarea.value );
    this.$textarea.data( namespace, this );
    this.$textarea.on( 'focus.' + namespace, $.proxy( this.initialize, this ) );
    this.contenteditable =  false ;
    this.contenteditable = this.$textarea.attr('contenteditable') == "true" ? true:false;
    return this;
}

Tagging.prototype.getValue = function() {

    var value = ''; 

   // value = this.textarea.value ;

   // return value;
    
    if (this.contenteditable) { 
        this.$textarea.children().each(function (idx, item) {

            //item = joms.util.emoji.replaceEmojiElementsToText($(item).clone(true));

            let text = item.textContent;
            if (value) {
                
                value += '\n' + text;
            } else {
                value = text
            }
        });
        if (this.$textarea.children().length == 0) {
            return this.$textarea.text();
        }

        if (!value) {
            value = $('.input.input-status.joms-textarea').text();
        } 

        return value;
    } else {
        value = this.textarea.value ;
        return value;
    }

}

Tagging.prototype.setValue = function(value) {
    
    if(this.contenteditable){ 
        //this.textarea.value =  value ;
        let html = "" ;
       
        var tags = this.tagsAdded  ;
        let linevalue  = "" ;
      
        if (tags.length ) {
           
            rMatch = '^';
            rReplace = '';
            start = 0;
    
            for ( i = 0; i < tags.length; i++ ) {
                tag = tags[i];
                rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
                rReplace += '$' + ( i * 2 + 1 ) + '<span contenteditable="false">' + tag.name + '</span>';

                //rReplace += '$' + ( i * 2 + 1 ) + ' '.repeat(tag.length );
                start = tag.start + tag.length;
            }
    
            rMatch = new RegExp( rMatch );
          
            value = value.replace( rMatch, rReplace );
          
          
        }
        let lines = value.split("\n");
        for ( j = 0; j < lines.length; j++ ) { // ignore emojis here 
            linevalue =  lines[j] ;
            
           
          
            html += '<div>'+linevalue+'</div>';
        }
        if(html == "<div><br></div>"){
            retrun ;
        }
        if(html ==""){
            html = "<div><br></div>";
        }
       
        var re = /(?:\ud83d\udc68\ud83c\udffc\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c\udffb|\ud83d\udc68\ud83c\udffd\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb\udffc]|\ud83d\udc68\ud83c\udffe\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb-\udffd]|\ud83d\udc68\ud83c\udfff\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb-\udffe]|\ud83d\udc69\ud83c\udffb\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffc-\udfff]|\ud83d\udc69\ud83c\udffc\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb\udffd-\udfff]|\ud83d\udc69\ud83c\udffc\u200d\ud83e\udd1d\u200d\ud83d\udc69\ud83c\udffb|\ud83d\udc69\ud83c\udffd\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb\udffc\udffe\udfff]|\ud83d\udc69\ud83c\udffd\u200d\ud83e\udd1d\u200d\ud83d\udc69\ud83c[\udffb\udffc]|\ud83d\udc69\ud83c\udffe\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb-\udffd\udfff]|\ud83d\udc69\ud83c\udffe\u200d\ud83e\udd1d\u200d\ud83d\udc69\ud83c[\udffb-\udffd]|\ud83d\udc69\ud83c\udfff\u200d\ud83e\udd1d\u200d\ud83d\udc68\ud83c[\udffb-\udffe]|\ud83d\udc69\ud83c\udfff\u200d\ud83e\udd1d\u200d\ud83d\udc69\ud83c[\udffb-\udffe]|\ud83e\uddd1\ud83c\udffb\u200d\ud83e\udd1d\u200d\ud83e\uddd1\ud83c\udffb|\ud83e\uddd1\ud83c\udffc\u200d\ud83e\udd1d\u200d\ud83e\uddd1\ud83c[\udffb\udffc]|\ud83e\uddd1\ud83c\udffd\u200d\ud83e\udd1d\u200d\ud83e\uddd1\ud83c[\udffb-\udffd]|\ud83e\uddd1\ud83c\udffe\u200d\ud83e\udd1d\u200d\ud83e\uddd1\ud83c[\udffb-\udffe]|\ud83e\uddd1\ud83c\udfff\u200d\ud83e\udd1d\u200d\ud83e\uddd1\ud83c[\udffb-\udfff]|\ud83e\uddd1\u200d\ud83e\udd1d\u200d\ud83e\uddd1|\ud83d\udc6b\ud83c[\udffb-\udfff]|\ud83d\udc6c\ud83c[\udffb-\udfff]|\ud83d\udc6d\ud83c[\udffb-\udfff]|\ud83d[\udc6b-\udc6d])|(?:\ud83d[\udc68\udc69])(?:\ud83c[\udffb-\udfff])?\u200d(?:\u2695\ufe0f|\u2696\ufe0f|\u2708\ufe0f|\ud83c[\udf3e\udf73\udf93\udfa4\udfa8\udfeb\udfed]|\ud83d[\udcbb\udcbc\udd27\udd2c\ude80\ude92]|\ud83e[\uddaf-\uddb3\uddbc\uddbd])|(?:\ud83c[\udfcb\udfcc]|\ud83d[\udd74\udd75]|\u26f9)((?:\ud83c[\udffb-\udfff]|\ufe0f)\u200d[\u2640\u2642]\ufe0f)|(?:\ud83c[\udfc3\udfc4\udfca]|\ud83d[\udc6e\udc71\udc73\udc77\udc81\udc82\udc86\udc87\ude45-\ude47\ude4b\ude4d\ude4e\udea3\udeb4-\udeb6]|\ud83e[\udd26\udd35\udd37-\udd39\udd3d\udd3e\uddb8\uddb9\uddcd-\uddcf\uddd6-\udddd])(?:\ud83c[\udffb-\udfff])?\u200d[\u2640\u2642]\ufe0f|(?:\ud83d\udc68\u200d\u2764\ufe0f\u200d\ud83d\udc8b\u200d\ud83d\udc68|\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc66\u200d\ud83d\udc66|\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc67\u200d\ud83d[\udc66\udc67]|\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d\udc66\u200d\ud83d\udc66|\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d[\udc66\udc67]|\ud83d\udc69\u200d\u2764\ufe0f\u200d\ud83d\udc8b\u200d\ud83d[\udc68\udc69]|\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc66\u200d\ud83d\udc66|\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d[\udc66\udc67]|\ud83d\udc68\u200d\u2764\ufe0f\u200d\ud83d\udc68|\ud83d\udc68\u200d\ud83d\udc66\u200d\ud83d\udc66|\ud83d\udc68\u200d\ud83d\udc67\u200d\ud83d[\udc66\udc67]|\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d[\udc66\udc67]|\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d[\udc66\udc67]|\ud83d\udc69\u200d\u2764\ufe0f\u200d\ud83d[\udc68\udc69]|\ud83d\udc69\u200d\ud83d\udc66\u200d\ud83d\udc66|\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d[\udc66\udc67]|\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d[\udc66\udc67]|\ud83c\udff3\ufe0f\u200d\u26a7\ufe0f|\ud83c\udff3\ufe0f\u200d\ud83c\udf08|\ud83c\udff4\u200d\u2620\ufe0f|\ud83d\udc15\u200d\ud83e\uddba|\ud83d\udc41\u200d\ud83d\udde8|\ud83d\udc68\u200d\ud83d[\udc66\udc67]|\ud83d\udc69\u200d\ud83d[\udc66\udc67]|\ud83d\udc6f\u200d\u2640\ufe0f|\ud83d\udc6f\u200d\u2642\ufe0f|\ud83e\udd3c\u200d\u2640\ufe0f|\ud83e\udd3c\u200d\u2642\ufe0f|\ud83e\uddde\u200d\u2640\ufe0f|\ud83e\uddde\u200d\u2642\ufe0f|\ud83e\udddf\u200d\u2640\ufe0f|\ud83e\udddf\u200d\u2642\ufe0f)|[#*0-9]\ufe0f?\u20e3|(?:[©®\u2122\u265f]\ufe0f)|(?:\ud83c[\udc04\udd70\udd71\udd7e\udd7f\ude02\ude1a\ude2f\ude37\udf21\udf24-\udf2c\udf36\udf7d\udf96\udf97\udf99-\udf9b\udf9e\udf9f\udfcd\udfce\udfd4-\udfdf\udff3\udff5\udff7]|\ud83d[\udc3f\udc41\udcfd\udd49\udd4a\udd6f\udd70\udd73\udd76-\udd79\udd87\udd8a-\udd8d\udda5\udda8\uddb1\uddb2\uddbc\uddc2-\uddc4\uddd1-\uddd3\udddc-\uddde\udde1\udde3\udde8\uddef\uddf3\uddfa\udecb\udecd-\udecf\udee0-\udee5\udee9\udef0\udef3]|[\u203c\u2049\u2139\u2194-\u2199\u21a9\u21aa\u231a\u231b\u2328\u23cf\u23ed-\u23ef\u23f1\u23f2\u23f8-\u23fa\u24c2\u25aa\u25ab\u25b6\u25c0\u25fb-\u25fe\u2600-\u2604\u260e\u2611\u2614\u2615\u2618\u2620\u2622\u2623\u2626\u262a\u262e\u262f\u2638-\u263a\u2640\u2642\u2648-\u2653\u2660\u2663\u2665\u2666\u2668\u267b\u267f\u2692-\u2697\u2699\u269b\u269c\u26a0\u26a1\u26a7\u26aa\u26ab\u26b0\u26b1\u26bd\u26be\u26c4\u26c5\u26c8\u26cf\u26d1\u26d3\u26d4\u26e9\u26ea\u26f0-\u26f5\u26f8\u26fa\u26fd\u2702\u2708\u2709\u270f\u2712\u2714\u2716\u271d\u2721\u2733\u2734\u2744\u2747\u2757\u2763\u2764\u27a1\u2934\u2935\u2b05-\u2b07\u2b1b\u2b1c\u2b50\u2b55\u3030\u303d\u3297\u3299])(?:\ufe0f|(?!\ufe0e))|(?:(?:\ud83c[\udfcb\udfcc]|\ud83d[\udd74\udd75\udd90]|[\u261d\u26f7\u26f9\u270c\u270d])(?:\ufe0f|(?!\ufe0e))|(?:\ud83c[\udf85\udfc2-\udfc4\udfc7\udfca]|\ud83d[\udc42\udc43\udc46-\udc50\udc66-\udc69\udc6e\udc70-\udc78\udc7c\udc81-\udc83\udc85-\udc87\udcaa\udd7a\udd95\udd96\ude45-\ude47\ude4b-\ude4f\udea3\udeb4-\udeb6\udec0\udecc]|\ud83e[\udd0f\udd18-\udd1c\udd1e\udd1f\udd26\udd30-\udd39\udd3d\udd3e\uddb5\uddb6\uddb8\uddb9\uddbb\uddcd-\uddcf\uddd1-\udddd]|[\u270a\u270b]))(?:\ud83c[\udffb-\udfff])?|(?:\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc65\udb40\udc6e\udb40\udc67\udb40\udc7f|\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc73\udb40\udc63\udb40\udc74\udb40\udc7f|\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc77\udb40\udc6c\udb40\udc73\udb40\udc7f|\ud83c\udde6\ud83c[\udde8-\uddec\uddee\uddf1\uddf2\uddf4\uddf6-\uddfa\uddfc\uddfd\uddff]|\ud83c\udde7\ud83c[\udde6\udde7\udde9-\uddef\uddf1-\uddf4\uddf6-\uddf9\uddfb\uddfc\uddfe\uddff]|\ud83c\udde8\ud83c[\udde6\udde8\udde9\uddeb-\uddee\uddf0-\uddf5\uddf7\uddfa-\uddff]|\ud83c\udde9\ud83c[\uddea\uddec\uddef\uddf0\uddf2\uddf4\uddff]|\ud83c\uddea\ud83c[\udde6\udde8\uddea\uddec\udded\uddf7-\uddfa]|\ud83c\uddeb\ud83c[\uddee-\uddf0\uddf2\uddf4\uddf7]|\ud83c\uddec\ud83c[\udde6\udde7\udde9-\uddee\uddf1-\uddf3\uddf5-\uddfa\uddfc\uddfe]|\ud83c\udded\ud83c[\uddf0\uddf2\uddf3\uddf7\uddf9\uddfa]|\ud83c\uddee\ud83c[\udde8-\uddea\uddf1-\uddf4\uddf6-\uddf9]|\ud83c\uddef\ud83c[\uddea\uddf2\uddf4\uddf5]|\ud83c\uddf0\ud83c[\uddea\uddec-\uddee\uddf2\uddf3\uddf5\uddf7\uddfc\uddfe\uddff]|\ud83c\uddf1\ud83c[\udde6-\udde8\uddee\uddf0\uddf7-\uddfb\uddfe]|\ud83c\uddf2\ud83c[\udde6\udde8-\udded\uddf0-\uddff]|\ud83c\uddf3\ud83c[\udde6\udde8\uddea-\uddec\uddee\uddf1\uddf4\uddf5\uddf7\uddfa\uddff]|\ud83c\uddf4\ud83c\uddf2|\ud83c\uddf5\ud83c[\udde6\uddea-\udded\uddf0-\uddf3\uddf7-\uddf9\uddfc\uddfe]|\ud83c\uddf6\ud83c\udde6|\ud83c\uddf7\ud83c[\uddea\uddf4\uddf8\uddfa\uddfc]|\ud83c\uddf8\ud83c[\udde6-\uddea\uddec-\uddf4\uddf7-\uddf9\uddfb\uddfd-\uddff]|\ud83c\uddf9\ud83c[\udde6\udde8\udde9\uddeb-\udded\uddef-\uddf4\uddf7\uddf9\uddfb\uddfc\uddff]|\ud83c\uddfa\ud83c[\udde6\uddec\uddf2\uddf3\uddf8\uddfe\uddff]|\ud83c\uddfb\ud83c[\udde6\udde8\uddea\uddec\uddee\uddf3\uddfa]|\ud83c\uddfc\ud83c[\uddeb\uddf8]|\ud83c\uddfd\ud83c\uddf0|\ud83c\uddfe\ud83c[\uddea\uddf9]|\ud83c\uddff\ud83c[\udde6\uddf2\uddfc]|\ud83c[\udccf\udd8e\udd91-\udd9a\udde6-\uddff\ude01\ude32-\ude36\ude38-\ude3a\ude50\ude51\udf00-\udf20\udf2d-\udf35\udf37-\udf7c\udf7e-\udf84\udf86-\udf93\udfa0-\udfc1\udfc5\udfc6\udfc8\udfc9\udfcf-\udfd3\udfe0-\udff0\udff4\udff8-\udfff]|\ud83d[\udc00-\udc3e\udc40\udc44\udc45\udc51-\udc65\udc6a-\udc6d\udc6f\udc79-\udc7b\udc7d-\udc80\udc84\udc88-\udca9\udcab-\udcfc\udcff-\udd3d\udd4b-\udd4e\udd50-\udd67\udda4\uddfb-\ude44\ude48-\ude4a\ude80-\udea2\udea4-\udeb3\udeb7-\udebf\udec1-\udec5\uded0-\uded2\uded5\udeeb\udeec\udef4-\udefa\udfe0-\udfeb]|\ud83e[\udd0d\udd0e\udd10-\udd17\udd1d\udd20-\udd25\udd27-\udd2f\udd3a\udd3c\udd3f-\udd45\udd47-\udd71\udd73-\udd76\udd7a-\udda2\udda5-\uddaa\uddae-\uddb4\uddb7\uddba\uddbc-\uddca\uddd0\uddde-\uddff\ude70-\ude73\ude78-\ude7a\ude80-\ude82\ude90-\ude95]|[\u23e9-\u23ec\u23f0\u23f3\u267e\u26ce\u2705\u2728\u274c\u274e\u2753-\u2755\u2795-\u2797\u27b0\u27bf\ue50a])|\ufe0f/g;
        // ignore emojis here fix colorful html too .

        var re = /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff])[\ufe0e\ufe0f]?(?:[\u0300-\u036f\ufe20-\ufe23\u20d0-\u20f0]|\ud83c[\udffb-\udfff])?(?:\u200d(?:[^\ud800-\udfff]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff])[\ufe0e\ufe0f]?(?:[\u0300-\u036f\ufe20-\ufe23\u20d0-\u20f0]|\ud83c[\udffb-\udfff])?)*/g;
       
        html = html.replace(re,(matched, index, original ) => {
           return joms.util.emoji.getIconByUni(matched);
             
        });
        this.$textarea.html(html);
       
        
    }else{
        this.textarea.value = value;
    }

}

Tagging.prototype.setSelectionRange = function(start, end){  

    if(this.contenteditable){
        // move caret in div.
        //this.textarea.setSelectionRange(tag.start + tmp.length, tag.start + tmp.length);
        range = document.createRange();
        range.selectNodeContents(this.textarea);
        var selection = window.getSelection();
        
      //  if (range) {
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
      //  }
    }else{

        this.textarea.setSelectionRange(start,end);
    }
}

Tagging.prototype.initialize = function() {
    var value, tags, match, start, i;

    this.dropdownIsOpened     = false;
    this.dropdownIsClicked    = false;
    this.dropdownSelectedItem = false;

    this.domPrepare();
    // this.inputPrepare();

    this.tagsAdded = [];
    value = '';

    if ( this.$textarea.data('initialValue') ) {
        value = this.getValue();
        tags = value.match( rTags );
        this.setValue(value.replace( rTags, '$2' ));
        //this.textarea.value = value.replace( rTags, '$2' );
        if ( tags && tags.length ) {
            for ( i = 0; i < tags.length; i++ ) {
                match = tags[i].match( rTag );
                start = value.indexOf( tags[i] );
                value = value.replace( tags[i], match[2] );
                this.tagsAdded.push({
                    id     : match[1],
                    name   : match[2],
                    start  : start,
                    length : match[2].length
                });
            }
        }
    }

    this.beautifierUpdate( value, this.tagsAdded );
    this.hiddenUpdate( value, this.tagsAdded );
    this.inputAutogrow();

    this.$textarea
        .off( 'focus.'   + namespace ).on( 'focus.'   + namespace, $.proxy( this.inputOnKeydown, this ) )
        .off( 'click.'   + namespace ).on( 'click.'   + namespace, $.proxy( this.inputOnKeydown, this ) )
        .off( 'keydown.' + namespace ).on( 'keydown.' + namespace, $.proxy( this.inputOnKeydown, this ) )
        .off( 'keyup.'   + namespace ).on( 'keyup.'   + namespace, $.proxy( this.inputOnKeyup, this ) )
        .off( 'input.'   + namespace ).on( 'input.'   + namespace, $.proxy( this.inputOnInput, this ) )
        .off( 'blur.'    + namespace ).on( 'blur.'    + namespace, $.proxy( this.inputOnBlur, this ) );

    this.$dropdown
        .off(  'mouseenter.' + namespace ).on( 'mouseenter.' + namespace, cssDropdownItem, $.proxy( this.dropdownOnMouseEnter, this ) )
        .off(  'mousedown.'  + namespace ).on( 'mousedown.'  + namespace, cssDropdownItem, $.proxy( this.dropdownOnMouseDown, this ) )
        .off(  'mouseup.'    + namespace ).on( 'mouseup.'    + namespace, cssDropdownItem, $.proxy( this.dropdownOnMouseUp, this ) );

    this.textarea.joms_beautifier = this.$beautifier;
    this.textarea.joms_hidden = this.$hidden;


    var that = this;
    this.textarea.joms_reset = function() {
        that.inputReset();
    };

};

Tagging.prototype.domPrepare = function() {
    this.$wrapper = this.$textarea.parent( cssWrapper );
    if ( !this.$wrapper.length ) {
        this.$textarea.wrap( '<div class="' + cssWrapper.substr(1) + '"></div>' );
        this.$wrapper = this.$textarea.parent();
    }

    this.$beautifier = this.$wrapper.children( cssBeautifier );
    if ( !this.$beautifier.length ) {
        //let cssTextarea = ".joms-textarea";
        this.$beautifier = $( '<div class="' + ".joms-textarea".substr(1).replace("."," ") + ' ' + cssBeautifier.substr(1) + '"></div>' );
        this.$beautifier.prependTo( this.$wrapper );
    }

    this.$hidden = this.$wrapper.children( cssHidden );
    if ( !this.$hidden.length ) {
        this.$hidden = $( '<input type="hidden" class="' + cssHidden.substr(1) + '">' );
        this.$hidden.appendTo( this.$wrapper );
    }

    this.$dropdown = this.$wrapper.children( cssDropdown );
    if ( !this.$dropdown.length ) {
        this.$dropdown = $( '<div class="' + cssDropdown.substr(1) + '"></div>' );
        this.$dropdown.appendTo( this.$wrapper );
    }
};

Tagging.prototype.inputPrepare = function() {

};

// @todo
Tagging.prototype.inputReset = function() {
    if ( this.tagsAdded ) {
        this.tagsAdded = [];
    }

    // console.log('inputReset');
    // this.tagsAdded = [];
    // this.$hidden.val();
    // this.$textarea.val();
    // this.$beautifier.html( text );
    // this.$textarea.trigger( 'reset.' + namespace );
};

Tagging.prototype.inputAutogrow = function() {
    var prevHeight = +this.$textarea.data( namespace + '-prevHeight' ),
        height;

    this.$wrapper.css({ height: prevHeight });
    this.$textarea.css({ height: '' });

    height = this.textarea.scrollHeight + 2;
    this.$textarea.css({ height: height });
    if ( height !== +prevHeight ) {
        this.$textarea.data( namespace + '-prevHeight', height );
    }

    this.$wrapper.css({ height: '' });
};

Tagging.prototype.inputOnKeydown = function( e ) { 
    // Catch dropdown navigation buttons.
    if ( this.dropdownIsOpened ) {
        if ([ VK_ENTER, VK_ESC, VK_KEYUP, VK_KEYDOWN ].indexOf( e.keyCode ) >= 0 ) {
            return false;
        }
    }

    // Reset input to initial state if Esc button is pressed.
    if ( e.keyCode === VK_ESC ) {
        this.inputReset();
        return false;
    }

    this.prevSelStart = this.textarea.selectionStart;
    this.prevSelEnd = this.textarea.selectionEnd;
};

Tagging.prototype.inputOnKeyup = function( e ) {
    if ( this.dropdownIsOpened ) {
        if ( e.keyCode === VK_KEYUP || e.keyCode === VK_KEYDOWN ) {
            this.dropdownChangeItem( e.keyCode );
            return false;
        }

        if ( e.keyCode === VK_ENTER ) {
            this.dropdownSelectItem();
            return false;
        }

        if ( e.keyCode === VK_ESC ) {
            this.dropdownHide();
            return false;
        }
    }
};

Tagging.prototype.inputOnInput = function() { 
    var value = this.getValue(),
        delta, tag, length, name, tmp, index, rMatch, rReplace, shift, i, j;

       
    // Shift tags position.
    if ( this.tagsAdded ) {

        // if text is selected (selectionStart !== selectionEnd)
        if ( this.prevSelStart !== this.prevSelEnd ) {
            for ( i = 0; i < this.tagsAdded.length; i++ ) {
                tag = this.tagsAdded[i];
                length = tag.start + tag.length;
                if (
                    // Intersection.
                    ( this.prevSelStart > tag.start && this.prevSelStart < length ) ||
                    ( this.prevSelEnd > tag.start && this.prevSelEnd < length ) ||
                    // Enclose.
                    ( tag.start >= this.prevSelStart && length <= this.prevSelEnd )
                ) {
                    this.tagsAdded.splice( i--, 1 );
                }
            }
        }

        delta = this.textarea.selectionStart - this.prevSelStart - ( this.prevSelEnd - this.prevSelStart );
        
        for ( i = 0; i < this.tagsAdded.length; i++ ) {
            tag = this.tagsAdded[i];

            // Tag's start is in right of or exactly at cursor position.
            if ( tag.start >= this.prevSelStart ) {
                tag.start += delta;
            } else {
                length = tag.start + tag.length;

                // Tag's end is in left of cursor position.
                if ( length < this.prevSelStart ) {
                    // do nothing

                // Cursor position is inside a tag.
                } else if ( length > this.prevSelStart ) {
                    // Not backspace.
                    if ( delta > 0 ) {
                        this.tagsAdded.splice( i--, 1 );
                    // Backspace.
                    } else if ( delta < 0 ) {
                        name = value.substring( tag.start, this.prevSelStart + delta );
                        index = name.split(' ').length - 1;
                        name = tag.name.split(' ');
                        name.splice( index, 1 );
                        name = name.join(' ');

                        tmp = tag.name.split(' ');
                        tmp = tmp.slice( 0, index );
                        tmp = tmp.join(' ');

                        rMatch = new RegExp( '^([\\s\\S]{' + tag.start + '})([\\s\\S]{' + ( tag.length + delta ) + '})' );
                        rReplace = '$1' + name;
                       // this.textarea.value = this.textarea.value.replace( rMatch, rReplace );
                        this.setValue (this.getValue().replace( rMatch, rReplace ));
                       
                        
                       
                        //value = this.textarea.value;
                        value = this.getValue();
                        shift = tag.length - name.length;
                        tag.name = name;
                        tag.length = name.length;

                        for ( j = i + 1; j < this.tagsAdded.length; j++ ) {
                            this.tagsAdded[j].start -= shift;
                        }

                        if ( !name.length ) {
                            this.tagsAdded.splice( i--, 1 );
                        }

                        i = this.tagsAdded.length;

                        this.setValue (value);
                        this.setSelectionRange(tag.start + tmp.length, tag.start + tmp.length);

                    }

                // Tag's end is exactly at cursor position... and a backspace is pressed.
                } else if ( delta < 0 ) {
                    name = tag.name.split(' ');
                    name.pop();
                    name = name.join(' ');
                    
                    tmp = tag.name.split(' ');
                    tmp = tmp.slice( 0, index );
                    tmp = tmp.join(' ');

                    rMatch = new RegExp( '^([\\s\\S]{' + tag.start + '})([\\s\\S]{' + ( tag.length + delta ) + '})' );
                    rReplace = '$1' + name;
                    //this.textarea.value = this.textarea.value.replace( rMatch, rReplace );
                    this.setValue (this.getValue().replace( rMatch, rReplace ));


                    
                    
                    
                   // value = this.textarea.value;
                   value = this.getValue();
                    shift = tag.length - name.length;
                    tag.name = name;
                    tag.length = name.length;

                    for ( j = i + 1; j < this.tagsAdded.length; j++ ) {
                        this.tagsAdded[j].start -= shift;
                    }

                    if ( !name.length ) {
                        this.tagsAdded.splice( i--, 1 );
                    }

                    i = this.tagsAdded.length;
                    this.setValue(this.getValue());
                    this.setSelectionRange(tag.start + tmp.length, tag.start + tmp.length);
                    //this.setSelectionRange(tag.start + name.length, tag.start + name.length);
                }
            }
        }
    }

    this.inputAutogrow();
   // if(!this.contenteditable){
        this.beautifierUpdate( value, this.tagsAdded );
    //}
    
    this.hiddenUpdate( value, this.tagsAdded || [] );
    this.dropdownToggle();
};

Tagging.prototype.inputOnBlur = function() {
    this.dropdownIsClicked || this.dropdownHide();
};

Tagging.prototype.beautifierUpdate = joms._.debounce(function( value, tags ) {
    var rMatch, rReplace, start, tag, i;

    if ( tags.length ) {
        rMatch = '^';
        rReplace = '';
        start = 0;

        for ( i = 0; i < tags.length; i++ ) {
            tag = tags[i];
            rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
            rReplace += '$' + ( i * 2 + 1 ) + '[b]' + tag.name + '[/b]&nbsp;&nbsp;';
            start = tag.start + tag.length;
        }

        rMatch = new RegExp( rMatch );
        value = value.replace( rMatch, rReplace );
    }

    value = value.replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
    value = value.replace( /\[(\/?b)\]/g, '<$1>' );
    value = value.replace( rHashTag, rHashTagReplace );
    value = value.replace( rEol, rEolReplace );

    this.$beautifier.html( value );

}, joms.mobile ? 100 : 1 );

Tagging.prototype.hiddenUpdate = joms._.debounce(function( value, tags ) {
    var rMatch, rReplace, start, tag, i;

    if ( tags.length ) {
        rMatch = '^';
        rReplace = '';
        start = 0;

        for ( i = 0; i < tags.length; i++ ) {
            tag = tags[i];
            rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
            rReplace += '$' + ( i * 2 + 1 ) + '@[[' + tag.id + ':contact:' + tag.name + ']]';
            start = tag.start + tag.length;
        }

        rMatch = new RegExp( rMatch );
        value = value.replace( rMatch, rReplace );
    }

    this.$hidden.val( value );

}, joms.mobile ? 500 : 50 );

Tagging.prototype.dropdownToggle = joms._.debounce(function() {
    var cpos   = this.textarea.selectionStart,
        substr = this.getValue().substr( 0, cpos ),
        index  = substr.lastIndexOf('@');
    
    if ( index < 0 || ++index >= cpos ) {
        this.dropdownHide();
        return;
    }

    substr = substr.substring( index, cpos );

    this.dropdownFetch( substr, joms._.bind( this.dropdownUpdate, this ) );

}, joms.mobile ? 1000 : 200 );

Tagging.prototype.dropdownFetch = function( keyword, callback, friends ) {
    var source  = ( window.joms_friends || [] ).concat( friends || [] ),
        added   = this.tagsAdded || [],
        matches = [],
        uniques = [],
        item, name, isAdded, that, i, j;

    // Map data-source.
    if ( source && source.length ) {
        keyword = keyword.toLowerCase();
        for ( i = 0; (i < source.length) && (matches.length < 20); i++ ) {
            item = source[i];
            name = ( item.name || '' ).toLowerCase();
            if ( name.indexOf( keyword ) >= 0 ) {
                isAdded = false;
                for ( j = 0; j < added.length; j++ ) {
                    if ( +item.id === +added[j].id ) {
                        isAdded = true;
                        break;
                    }
                }

                if ( !isAdded && uniques.indexOf( +item.id ) < 0 ) {
                    uniques.push( +item.id );
                    matches.push({
                        id: item.id,
                        name: item.name,
                        img: item.avatar
                    });
                }
            }
        }
    }

    matches.sort(function( a, b ) {
        if ( a.name < b.name ) return -1;
        if ( a.name > b.name ) return 1;
        return 0;
    });

    callback( matches );

    if ( typeof this.fetcher === 'function' && !friends ) {
        that = this;
        this.fetcher(function( friends ) {
            friends || (friends = []);
            that.dropdownFetch( keyword, joms._.bind( that.dropdownUpdate, that ), friends );
        });
    }
};

Tagging.prototype.dropdownUpdate = function( matches ) {
    var html, item, cname, i, length;

    if ( !( matches && matches.length ) ) {
        this.dropdownHide();
        return;
    }

    html = '';
    cname = cssDropdownItem.substr(1);
    length = Math.min( 10, matches.length );
    for ( i = 0; i < length; i++ ) {
        item = matches[ i ];
        html += '<a href="javascript:" class=' + cname + ' data-id="' + item.id +  '" data-name="' + item.name + '">';
        html += '<img src="' + item.img + '">' + item.name + '</a>';
    }

    this.dropdownShow( html );
};

Tagging.prototype.dropdownShow = function( html ) {
    this.$dropdown.html( html ).show();
    this.dropdownIsOpened = true;
    this.dropdownSelectedItem = false;
};

Tagging.prototype.dropdownHide = function() {
    this.$dropdown.hide();
    this.dropdownIsOpened = false;
};

Tagging.prototype.dropdownOnMouseEnter = function( e ) {
    this.dropdownChangeItem( e );
};

Tagging.prototype.dropdownOnMouseDown = function() {
    this.dropdownIsClicked = true;
};

Tagging.prototype.dropdownOnMouseUp = function( e ) {
    this.dropdownSelectItem( e );
    this.dropdownIsClicked = false;
    this.dropdownHide();
};

Tagging.prototype.dropdownChangeItem = function( e ) {
    var className = cssDropdownItemActive.substr(1),
        elem, sibs, next;

    if ( typeof e !== 'number' ) {
        elem = this.dropdownSelectedItem = $( e.target );
        sibs = elem.siblings( cssDropdownItemActive );
        elem.addClass( className );
        sibs.removeClass( className );
        return;
    }

    elem = this.$dropdown.children( cssDropdownItemActive );
    if ( !elem.length ) {
        elem = this.dropdownSelectedItem = this.$dropdown.children()[ e === VK_KEYUP ? 'last' : 'first' ]();
        elem.addClass( className );
        return;
    }

    next = elem[ e === VK_KEYUP ? 'prev' : 'next' ]();
    elem.removeClass( className );
    if ( next.length ) {
        this.dropdownSelectedItem = next;
        next.addClass( className );
    } else {
        this.dropdownSelectedItem = false;
    }
};

Tagging.prototype.dropdownSelectItem = function( e ) {
    var el       = e ? $( e.currentTarget ) : this.dropdownSelectedItem,
        id       = el.data('id'),
        name     = el.data('name'),
        cpos     = this.textarea.selectionStart,
        substr   = this.getValue().substr( 0, cpos ),
        index    = substr.lastIndexOf('@'),
        re, value;

    this.tagsAdded || (this.tagsAdded = []);
    this.tagsAdded.push({
        id     : id,
        name   : name,
        start  : index,
        length : name.length
    });

    re = new RegExp( '^([\\s\\S]{' + index + '})[\\s\\S]{' + ( cpos - index ) + '}' );
    value = this.getValue().replace( re, '$1' + name );

    this.setValue(value);
    
    this.inputAutogrow();
    //if(!this.contenteditable){
        this.beautifierUpdate( value, this.tagsAdded );
    //}
    
    this.hiddenUpdate( value, this.tagsAdded );
    this.dropdownHide();
    if(this.contenteditable){
        // move caret in div.
        //this.textarea.setSelectionRange(tag.start + tmp.length, tag.start + tmp.length);
        range = document.createRange();
        range.selectNodeContents(this.$textarea.children(":last").get(0));
        var selection = window.getSelection();
        
      //  if (range) {
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
      //  }
    }
};

// Public.
Tagging.prototype.clear = function() {
    this.tagsAdded = [];
    this.$textarea && this.$textarea.val('');
    this.$hidden && this.$hidden.val('');
    this.$beautifier && this.$beautifier.empty();
};

// Exports.
return function( textarea, extraFetch ) {
    var instance = $( textarea ).data( namespace );

    if ( instance ) {
        return instance;
    } else {
        return new Tagging( textarea, extraFetch );
    }
};

});
