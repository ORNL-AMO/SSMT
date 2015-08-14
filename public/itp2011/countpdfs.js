// $Id: pdf.js,v 1.3 2004/03/09 13:56:54 powem Exp $

// find pdf links on a page and add an onclick event to them
// Fri 8/18/06

/* this script dynamically adds onClick events to links on the
 * page.  it does this by looping through the document.links array
 * and checking for links to .pdf files.  any link to a .pdf that
 * doesn't already have an onclick event has one assigned to it.
 * 
 * the event calls a function that assigns the pathname of the link
 * to the URL (DCS.dcsuri) and the text of the link (the descriptive
 * text that appears on the page) to the page title (WT.ti).
 *
 * then, the dcsMultiTrack() function is called.
 *
 * the code has to be loaded at the bottom of the page, after the links 
 * array has been filled.  any links that load after the code runs will
 * not be in the array and will not be checked.  other than that, 
 * nothing bad will happen.
 *
 */

findDownloads();

function findDownloads(){

	var l = document.links;

	function check(){
		var dest = "/" + this.pathname;
		var ghost = this.hostname;
		var title = this.firstChild.data;
var
doms="www.eere.energy.gov,www1.eere.energy.gov,www.hydrogen.energy.gov,www.energysavers.gov,www.solardecathlon.org,apps3.eere.energy.gov,apps1.eere.energy.gov,apps2.eere.energy.gov,afdc.energy.gov,www.windpoweringamerica.gov,www.loseyourexcuse.gov,www.eecbg.energy.gov,eeftp.ee.doe.gov,techportal.eere.energy.gov,www.solardecathlon.gov,annualmeritreview.energy.gov"; //track additional domains by placing them here, delimited by commas (ie "www.eere.energy.gov,www.google.com" would track files on either domain)



	    var aDoms=doms.split(',');
	    for (var i=0;i<aDoms.length;i++){
	        if (ghost.indexOf(aDoms[i])!=-1){
              	dcsMultiTrack('DCS.dcsuri',dest,'DCS.dcssip',ghost,'WT.ti',title,'DCSext.filedownload','filedownload','DCSext.filename',ghost+dest);
            }
        }
    }

	for (i = 0; i < l.length; i++){
		if(l[i].pathname.match(/\.pdf|\.doc|\.xls|\.ppt|\.zip|\.exe|\.dat|\.swf|\.wvx|\.wax|\.asf|\.asx|\.wma|\.mp4|\.mp3|\.m3u|\.mpg|\.swf|\.avi|\.wmv/)){
			if(!l[i].onclick){
				l[i].onclick=check;
			}
		}
	}
} // end findPDF


