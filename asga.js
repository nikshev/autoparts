jQuery(document).ready(function(){

    jQuery("#car").change(function(){
        jQuery("body").addClass("loading"); 
        $id = jQuery(this).val();
        jQuery.ajax({
            type:"POST",
            url:"http://asga.com.ua/adsearch/index/Models",
            data:"id="+$id,
            cache:"false",
            success:function(html){
                jQuery("body").removeClass("loading");
                jQuery("#model").html(html);
            }
            });
        });

   jQuery("#model").change(function(){
        jQuery("body").addClass("loading"); 
        $id = jQuery(this).val();
        jQuery.ajax({
            type:"POST",
            url:"http://asga.com.ua/adsearch/index/Years",
            data:"id="+$id,
            cache:"false",
            success:function(html){
                jQuery("body").removeClass("loading");
                jQuery("#year").html(html);
            }
            });
        });

    jQuery("#find").click(function(){
        $car = jQuery("#car").val();
        $model =jQuery("#model").val(); 
        $year =jQuery("#year").val(); 
        $alternator=jQuery("#alternator").val(); 
        $starter=jQuery("#starter").val(); 
        $compr=jQuery("#compr").val(); 
        $tcompr=jQuery("#tcompr").val(); 
        $oem=jQuery("#oem").val(); 
        jQuery("body").addClass("loading"); 
        jQuery.ajax({
            type:"POST",
            url:"http://asga.com.ua/adsearch/index/Find",
            data:"car="+$car+"&model="+$model+"&year="+$year+"&alternator="+$alternator+"&starter="+$starter+"&compr="+$compr+"&tcompr="+$tcompr+"&oem="+$oem,
            cache:"false",
            success:function(html){
                jQuery("body").removeClass("loading");
                jQuery("#search").html(html);
            }
            });
        });

    });  
    
    function getAnalogs($sku)
    {
      //jQuery("body").addClass("loading"); 
      jQuery.ajax({
                   type:"POST",
                   url:"http://asga.com.ua/adsearch/index/Analog",
                   data:"sku="+$sku,
                   cache:"false",
                   success:function(html){
                  jQuery("body").removeClass("loading");
                  jQuery("#analogs").html(html);
                 }
                });
    }
    
