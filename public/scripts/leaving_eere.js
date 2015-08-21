document.write('<link rel="stylesheet" href="https://www1.eere.energy.gov/includes/scripts/jquery/reveal/reveal.css"/>');

document.write('<script src="https://www1.eere.energy.gov/includes/scripts/jquery/reveal/jquery.reveal.js" type="text/javascript"></script>');

document.write('<style type="text/css">a.redirectLink:link {color:#BA5016;text-decoration:none;font-size:1.5em;} a.redirectLink:visited {color:#BA5016;} a.redirectLink:hover {color:#BA5016;text-decoration:underline;font-size:1.5em;} a.redirectLink:active {color:#BA5016;} a.modalNavButton:link {color:#000;text-decoration:none; font-weight:bold;} a.modalNavButton:visited {color:#000;} a.modalNavButton:hover {color:#BA5016;text-decoration:underline; font-weight:bold;} a.modalNavButton:active {color:#BA5016;} hr.modalHR { border: 0; height: 0; border-top: 1px solid rgba(0, 0, 0, 0.1); border-bottom: 1px solid rgba(255, 255, 255, 0.3); } </style>');

jQuery(document).ready(function() {

//check if a val is in an array (substring)
    function isInArray(arr, val) {
        blnFound = false;
        for (var i = 0; i < arr.length; i++) {
            if (val.indexOf(arr[i]) >= 0)
                blnFound = true;
        }
        return blnFound;
    }

    jQuery('body').append('<div id="leavingEEREModal" class="reveal-modal"><h1>You are now leaving a U.S. Department of Energy website.</h1><p>Click on the link below to continue, or you may select Cancel.</p><p><a href="#" id="leavingEEREModalURL" class="redirectLink">URL</a></p><br/><hr class="modalHR"/><br/><p><a href="#" id="cancelTimeout" class="modalNavButton"><img src="https://www1.eere.energy.gov/images/modal_arrow_left.gif" alt="Cancel"/> Cancel</a><a href="#" id="continueExternal" class="modalNavButton" style="float:right">Continue to this site <img src="https://www1.eere.energy.gov/images/modal_arrow_right.gif" alt="Continue to this site"/></a></p></div>');

// Creating custom :external selector
    jQuery.expr[':'].external = function(obj){

//Check if the hostname of the object is in the internalSites array
        var internalSites = ["energy.gov", "doe.gov", "energysavers.gov", "energycodes.gov", "loseyourexcuse.gov", "solardecathlon.gov", "windpoweringamerica.gov", "nrel.gov"]; //remove NREL on go live

        if (!(isInArray(internalSites, obj.hostname.toLowerCase())) && (obj.href.toLowerCase().indexOf("history.back") == -1) && (obj.href.toLowerCase().indexOf("mailto:") == -1) && (obj.href.toLowerCase().indexOf("javascript:") == -1) && (obj.href.toLowerCase() != "") ) {
            return true;
        }
        else return false;
    };

// Bind the click event to the cancel button
    jQuery('#cancelTimeout').click(function (e) {
        e.preventDefault();
// close the modal dialog
        jQuery('#leavingEEREModal').trigger('reveal:close');
    });

// This code will reveal the dialog when an external link
// is clicked by adding the 'external' CSS class to all external links
    jQuery('a:external').click(function(e) {
        e.preventDefault();

// setup link and text on the modal dialog
        var href = this.href;
        var displayhref = this.href;
        if (displayhref.length > 75) {
            displayhref = displayhref.substring(0, 74) + "...";
        }
        jQuery('#leavingEEREModalURL').attr('href', href);
        jQuery('#leavingEEREModalURL').text(displayhref);
        jQuery('#leavingEEREModalURL').click(function () {
            jQuery('#leavingEEREModal').trigger('reveal:close');
            window.location.href = href;
        });

// Show the modal
        jQuery('#leavingEEREModal').reveal();

// setup Continue button
        jQuery('#continueExternal').click(function () {
            jQuery('#leavingEEREModal').trigger('reveal:close');
            jQuery('#continueExternal').attr('href', href);
            window.location.href = href;
        });

        return false;
    });
});