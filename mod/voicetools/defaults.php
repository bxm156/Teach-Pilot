<?php 

   if (empty($CFG->voicetools_initialdisable)) {
       set_field('modules', 'visible', 0, 'name', 'voicetools');  // Disable it by default               
       set_config('voicetools_initialdisable', 1);
   }
   else if ($CFG->voicetools_initialdisable == 1 ){//we make sure that the voicetools module is disabled
       set_field('modules', 'visible', 0, 'name', 'voicetools');  // Disable it by default 
   }

?>
