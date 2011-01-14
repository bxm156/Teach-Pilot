<?php

/* Moodle Google (Moogle) block version 2007101509 : Google Search Services (Web, Image, Video, Book, Blog, News, Map, Custom Website) as a Moodle Block
 * 
 * @author Yajuvendrasinh V Mahida <yaju_mahida@yahoo.com>
 * 
 * @package block_moodle_google
 * 
 * This block is free software. you can redistribute it and/or modify it.
 */   

// To make any changes to the details/infomration/names do this in the language file

//----------------------------------------------------------------------------------------------------------
// The Main Moodle Google Search class
class block_moodle_google extends block_base {

    //------------------------------------------------------------------------------------------------------
    function init() {
        $this->title = get_string('blocktitle','block_moodle_google');
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2007101509;
      
      }
     
    //------------------------------------------------------------------------------------------------------   
    function has_config() {
        return true;
    }
        

    //------------------------------------------------------------------------------------------------------   
    function instance_allow_config() {
        return true;
    }
     
    //------------------------------------------------------------------------------------------------------   
    function preferred_width() {
         return 190;    // The preferred value is in pixels
    }
  
    //-------------------------------------------------------------------------------------------------------
    function get_content() {
    
        
    // Necessary for Access to Global Settings needed
    global $USER, $CFG, $COURSE;
    
    // If content has already been generated, don't waste time doing that
        if ($this->content !== NULL) {
            return $this->content;
        } 
                
    //-------------------------------------------------------------------------------------------------------
    // Fetching Values From Global Configuration & Instance Configuration
           
        $google_api_key = $CFG->block_moodle_google_api_key;
        $login_require_status=$CFG->login_require;
        $guest_block_available=$CFG->guest_avail_block;
       
        $default_search_string = $this->config->block_moodle_google_search_string; 
        $localwebsite_label = $this->config->block_moodle_google_website_label;
        $localwebsite = $this->config->block_moodle_google_user_website;
        $local_center_point = $this->config->block_moodle_google_center_point;
                      
    //-------------------------------------------------------------------------------------------------------
    // Logic for checking that the Authenticated User Login is required for this block or not
          if ($login_require_status==on){
               if (!isloggedin()) 
               {return false;}
               }
                   
    
    // Logic for checking that the Guest will be able to use this block ? If ticked 
          if ($guest_block_available==on){
               if (isguestuser()) 
               {return false;}
               }
            
     
     
     // Load Moogle Logo
        
          if (empty($CFG->block_logo)){
          $load_moogle_logo = '<div align="center" style="padding-top:3px;padding-bottom:3px;">
                              <span id="mooglelogo" style="width:200px;height:90px;">
                               <img src="'.$CFG->wwwroot.'/blocks/moodle_google/img/moogle.png" width="200" height="90" border="0" alt="Moogle Logo"></span>
                               </div>';
               }
               else{
          $load_moogle_logo = '<br>';
               }                      
    
    //-------------------------------------------------------------------------------------------------------
    // Logic for Google Search Options Fetch values from Instance Configuration and Show Accroding to that
                                                                                                          
        
        // SiteSearch()     - Constructor to provide results from Google Custom Webstie Search
            if ($this->config->usersitesearch_value==on){
               $usersitesearch_show =  'var siteSearch = new GwebSearch();
                                        siteSearch.setUserDefinedLabel("'.$localwebsite_label.'");
                                        siteSearch.setSiteRestriction("'.$localwebsite.'");
                                        YajuGoogleSearchControl.addSearcher(siteSearch, options)';
               }
               
        // LocalSearch()    - Constructor to provide results from Google Local Search    
            if ($this->config->localsearch_value==on){
               $localsearch_show =  'YajuGoogleSearchControl.addSearcher(localSearch,options)';
               }
            
        // WebSearch()      - Constructor to provide results from Google Web Search
            if ($this->config->websearch_value==on){
               $websearch_show =  'YajuGoogleSearchControl.addSearcher(new google.search.WebSearch(),options)';
               }
        
        // ImageSearch()    - Constructor to provide results from Google Image Search
            if ($this->config->imagesearch_value==on){
               $imagesearch_show =  'YajuGoogleSearchControl.addSearcher(new google.search.ImageSearch(),options)';
               }
    
        // VideoSearch()    - Constructor to provide results from Google Video Search
            if ($this->config->videosearch_value==on){
               $videosearch_show =  'YajuGoogleSearchControl.addSearcher(new google.search.VideoSearch(),options)';
               }
        
        // NewsSearch()     - Constructor to provide results from Google News Search 
            if ($this->config->newssearch_value==on){
               $newssearch_show =  'YajuGoogleSearchControl.addSearcher(new google.search.NewsSearch(),options)';
               }
    
           // BookSearch()     - Constructor to provide results from Google Book Search 
            if ($this->config->booksearch_value==on){
               $booksearch_show =  'YajuGoogleSearchControl.addSearcher(new google.search.BookSearch(),options)';
               }
    
           // BlogSearch()     - Constructor to provide results from Google Blog Search
            if ($this->config->blogsearch_value==on){
               $blogsearch_show =  'YajuGoogleSearchControl.addSearcher(new google.search.BlogSearch(),options)';
               }
    
      
    //-------------------------------------------------------------------------------------------------------
    // Logic for Google Search Tabbed Mode Layout/Default Expandable Bullet/Twiddle Layout
    // If it is checked it will show Tabbed Mode Layout result
        
         $drawOptionTABBED = empty($this->config->tabbedmode)?'':'drawOptions.setDrawMode(GSearchControl.DRAW_MODE_TABBED)';       
         
         
    //-------------------------------------------------------------------------------------------------------
    // Google Search Result Set Size FOUR/EIGHT Logic Implementation - 
    // If Large is selected it will show 8 results and if small is selected will show 4 results                 
          
           if($this->config->number_of_result==Large_Result) {
                $ResultSize= 'YajuGoogleSearchControl.setResultSetSize(GSearch.LARGE_RESULTSET)'; 
           } else if($this->config->number_of_result==Small_Result) {
                $ResultSize= 'YajuGoogleSearchControl.setResultSetSize(GSearch.SMALL_RESULTSET)'; };
          
                   
    //-------------------------------------------------------------------------------------------------------
    //  Google Search Result - Expansion Mode Logic  - Closed/Partial/Open
    
            if($this->config->block_moodle_google_result_expand_mode==Closed) {
                $set_expand_mode_status= 'options.setExpandMode(GSearchControl.EXPAND_MODE_CLOSED)'; 
            }else if($this->block_moodle_google_result_expand_mode==Partial) {
                $set_expand_mode_status= 'options.setExpandMode(GSearchControl.EXPAND_MODE_PARTIAL)'; 
            }else if($this->block_moodle_google_result_expand_mode==Open) {
                $set_expand_mode_status= 'options.setExpandMode(GSearchControl.EXPAND_MODE_OPEN)'; };    
                 
    //-------------------------------------------------------------------------------------------------------
    // Begin Main Part...  
        
         
        $this->content = new stdClass;
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content->text   = '';
            return $this->content;
        }       
        
    // Check The Google Ajax Search API Key in Global Configuration is applied or not and generate error message in block's footer
     
            if ($google_api_key==null){
                $this->content->footer .= '<center><table border = "0" width = "85%">';
                $this->content->footer .= '<tr>';
                $this->content->footer .=  '<td align = left>';
                $this->content->footer .= helpbutton('api_key_error', 'Google API Key is not set !', 'moodle_google_help', true, false, '', true);
                $this->content->footer .= '<BLINK><FONT SIZE = 1><B>Google API Key is not set !</B></BLINK>';
                $this->content->footer .= '</td>';
                $this->content->footer .=  '<tr>';
                $this->content->footer .=  '</table></center>';
                }
     
    // Check The Google Search Options in Instance Configuration is applied or not and generate error message in block's footer
        
            if (!$this->config->usersitesearch_value==on && !$this->config->localsearch_value==on && !$this->config->websearch_value==on && !$this->config->imagesearch_value==on && !$this->config->videosearch_value==on && !$this->config->newssearch_value==on && !$this->config->booksearch_value==on && !$this->config->blogsearch_value==on){
                $this->content->footer .= '<center><table border = "0" width = "85%">';
                $this->content->footer .= '<tr>';
                $this->content->footer .=  '<td>';
                $this->content->footer .= helpbutton('srch_optn_wrng', 'Search Services are not Set !', 'moodle_google_help', true, false, '', true);
                $this->content->footer .= '<BLINK><FONT SIZE = 1><B>Search Services are not Set !</B></FONT></BLINK>';
                $this->content->footer .= '</td>';
                $this->content->footer .=  '<tr>';
                $this->content->footer .=  '</table></center>';
               }
        
    // Generating Google Ajax Search Control Inside The Block  
    
        $this->content->text .= '
              
             
            <!This is the default Cascading Style Sheets from Google>
            <link href="http://www.google.com/uds/css/gsearch.css" type="text/css" rel="stylesheet" />
        
            <!Uncomment the following line to load css from moodle_google/css/gsearch.css. But it dont shows the Bullets/Twiddle.>
            <!link href="'.$CFG->wwwroot.'/blocks/moodle_google/css/gsearch.css" type="text/css" rel="stylesheet" />
           
    
                <style type="text/css"> 
          
                    .gsc-control { 
          
                            margin: 3px;
                            width : 190px;
                                 }
          
                </style>  
         
    <!Load the Ajax Script from Google using the Google API Key>
    <script src="http://www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key='.$google_api_key.'" type="text/javascript"></script>
    <script language="Javascript" type="text/javascript">
       
    
        // The following term CDATA is used about text data that should not be parsed by the XML parser.
            //<![CDATA[

    
        // Loading the Google AJAX Search API version 1.0 from Google using the API Key
           google.load("search", "1");

        function OnLoad() {
      
        // Creating a Yaju Google Search Control
           var YajuGoogleSearchControl = new google.search.SearchControl();

        //  Upon search completion it will show the desired expansion mode for search results.
        //  expandMode - supplies the expansion mode for the associated searcher results section.
        // o GSearchControlgoogle.search.SearchControl.EXPAND_MODE_CLOSED - The results section is closed showing no results. Clicking on the twiddle allows the                user to "open" the results section seeing all results returned for the current search.
        // o GSearchControlgoogle.search.SearchControl.EXPAND_MODE_OPEN - The results section is open showing all results.
        // o GSearchControlgoogle.search.SearchControl.EXPAND_MODE_PARTIAL - The results section is partially open showing a small fraction of the results.                     Typically this means that only a single result is displayed.
            
            var options = new GsearcherOptions();
            
                '.$set_expand_mode_status.';
                  
        // Creating  a Custom Websearch for Specific Website
            
             '.$usersitesearch_show.'
              
        
                      
            var localSearch = new google.search.LocalSearch(); 
        
        // Fetch it from configuration editor and Putting all the different Google Search Result
                                     
             '.$localsearch_show.'
             
             '.$websearch_show.'
             
             '.$imagesearch_show.'
                                  
             '.$videosearch_show.'
           
             '.$newssearch_show.'
           
             '.$booksearch_show.'
           
             '.$blogsearch_show.'
           
            
      
        // Setting the Center Point for Local Search
            localSearch.setCenterPoint("'.$local_center_point.'");

        // Setting the draw mode for the Google search
            var drawOptions = new GdrawOptions();

        //Google Search Result Set Size FOUR/EIGHT Logic Implementation
            '.$ResultSize.'
      
        // Google Search Tabbed Mode Logic Implementation
            '.$drawOptionTABBED.'
    
        // Tell the searcher to draw itself and tell it where to attach
            YajuGoogleSearchControl.draw(document.getElementById("YajuGoogleSearchControl"), drawOptions);
          
        // Executing an inital search using default search string
            YajuGoogleSearchControl.execute("'.$default_search_string.'");
                        
                        }
                        
            google.setOnLoadCallback(OnLoad);

            //]]>  
    
    </script>
    
         
    <!Load Moodle Google Logo>
    
        '.$load_moogle_logo.'
        
     
    <!Load Google Search Control>
    
        <div id="YajuGoogleSearchControl"></div>';
     
           return $this->content;                                                                     
     
    }                                                 
}
?>