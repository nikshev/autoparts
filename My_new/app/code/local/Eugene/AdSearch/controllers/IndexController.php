<?php
/**
* Plugin for search autoparts
*/
class Eugene_AdSearch_IndexController extends Mage_Core_Controller_Front_Action{
    protected $_itemCollection = null;
   
   /**
    * Index action
   */   
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Расширенный поиск"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Главная"),
                "title" => $this->__("Главная"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("advanced search", array(
                "label" => $this->__("Расширенный поиск"),
                "title" => $this->__("Расширенный поиск")
		   ));

      $this->renderLayout(); 
	  
    }
  
   /**
    * Models action (I mean action which search cars model by car id)
   */ 
   public function ModelsAction(){
     $read = Mage::getSingleton('core/resource')->getConnection('core_read');
	 $string="";
	 if (isset($_POST['id'])) {
     $sql = 'SELECT TYP_MOD_ID,TEXT_TEXT FROM mg_link WHERE MOD_MFA_ID='.intval($_POST['id'])).
            ' GROUP BY TYP_MOD_ID,TEXT_TEXT'.
            ' ORDER BY TYP_MOD_ID';
     $result = $read->query($sql);
     $string='<option value="">Choose model please</option>'; 
     
     if (!$result) {
      return $string;
     }

     while ($row = $result->fetch(PDO::FETCH_ASSOC)){
      $start=strpos($row['TEXT_TEXT'],' ',0);
      $string= $string.'<option value="'.$row['TYP_MOD_ID'].'">'.substr($row['TEXT_TEXT'],$start+1).'</option>';
     }
    }
     echo $string;
     
    }
   
   /**
    * Years action (action which search cars model years by model id)
   */  
   public function YearsAction(){
     $read = Mage::getSingleton('core/resource')->getConnection('core_read');
	 $string="";
	 if (isset($_POST['id'])){
     $sql = 'SELECT TYP_PCON_START,TYP_PCON_END FROM `mg_link` WHERE TYP_MOD_ID='.intval($_POST['id']).
            ' GROUP BY TYP_PCON_START,TYP_PCON_END '.
            ' ORDER BY TYP_PCON_START';
     $result = $read->query($sql);
     
     while ($row = $result->fetch(PDO::FETCH_ASSOC)){
      $start_year=substr($row['TYP_PCON_START'],0,4);
      $start_month=substr($row['TYP_PCON_START'],4);
      if (isset($row['TYP_PCON_END']))
       {
         $end_year=substr($row['TYP_PCON_END'],0,4);  
         $end_month=substr($row['TYP_PCON_END'],4);
         $string= $string.'<option value="'.$row['TYP_PCON_START'].'-'.$row['TYP_PCON_END'].'">'.$start_month.'.'.$start_year.'-'.$end_month.'.'.$end_year.'</option>';
       }
      else
       $string=$string.'<option value="'.$row['TYP_PCON_START'].'-'.'">'.$start_month.'.'.$start_year.'-'.'</option>';
      }
	 }
       echo $string;
    }

   /**
    * Find action (action which search auto parts)
   */  
  public function FindAction(){

   $type='10';
   if (isset($_POST['alternator']))
    $type='12';
   else if (isset($_POST['starter'])) 
    $type='10';
   else if (isset($_POST['compr'])) 
    $type='14';      
   else if (isset($_POST['tcompr'])) 
    $type='19';
    

   //echo "car=".$_POST['car']." model=".$_POST['model']." year=".$_POST['year']." type=".$type." oem=".$_POST['oem'];
   if (!isset($_POST['oem']))
    $_items = $this->getItems(addslashes($_POST['car']),addslashes($_POST['model']),addslashes($_POST['year']),$type);
   else
    $_items = $this->getItemsByOem(addslashes($_POST['oem']),$type);
    
   $string2='';
   $string='<div class="block">
    <div class="block-title">
        <strong><span>'.$this->__('Search results type=').'</span></strong>
    </div>
    <div class="block-content">
        <ol class="mini-products-list">';
        foreach ($_items->getItems() as $_item): 
         $product = Mage::getModel('catalog/product')->load($_item->getId());
         $string2.='<li class="item">
                <div class="product">
                    <a href="'.$_item->getProductUrl().'" title="'.$product->getName().
                    '" class="product-image" style="width:100px;"><img src="'.Mage::helper('catalog/image')->init($product, 'small_image')->resize(100).
                    '" width="100" height="100" alt="'.$product->getName().'" /></a>
                    <div class="product-details">
                        <p class="product-name" style="margin-left:12%;"><a href="'.$product->getProductUrl().'">'.$product->getName().'</a></p>';
                      $string2.='
                    </div>
                </div>
            </li>';
        endforeach;
       $string2.='
        </ol>
    </div>
</div>';

   echo $string.$string2;
  }
  
  /**
    * get items by car,model,year
   */ 
  public function getItems($car,$model,$year,$type)
    {
        if (!isset($car)||!isset($model))
            return false;
        if (is_null($this->_itemCollection)) {
        
        //Try to find all LA_ART_ID
        //array('eq' => $valueId)
        $start_year=substr($year,0,6);
        $end_year=substr($year,0,7);
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (isset($end_year))
         $sql = ' SELECT LA_ART_ID FROM `mg_link` WHERE MOD_MFA_ID='.$car.
                ' AND TYP_MOD_ID='.$model.
                ' AND TYP_PCON_START='.$start_year.
                ' AND TYP_PCON_END='.$end_year;
        else
         $sql = ' SELECT LA_ART_ID FROM `mg_link` WHERE MOD_MFA_ID='.$car.
                ' AND TYP_MOD_ID='.$model.
                ' AND TYP_PCON_START='.$start_year;

        $result = $read->query($sql);
        $i=0;
        while ($row = $result->fetch(PDO::FETCH_ASSOC)){
         $art_id_array[$i]=array('attribute'=>'la_art_id','eq'=>$row['LA_ART_ID']);
         $i++;
        }
         $art_id_array[$i]=array('attribute'=>'category_id','eq'=>$type);
        // print_r($art_id_array);
        //Try to get collection with all la_art_id
        // $art_id_array[0]=array('attribute'=>'la_art_id','eq'=>'1017743');
        // $art_id_array[1]=array('attribute'=>'la_art_id','eq'=>'1907281');
         $this->_itemCollection = $this->getItemsCollection($art_id_array,'');
         //$this->_itemCollection = $this->getItemsCollection(array(array('attribute'=>'la_art_id','eq'=>1017743)));
       }
 
        return $this->_itemCollection;
    }

  /**
    * get items collection by art_id_array and oem
   */
   public function getItemsCollection($art_id_array,$oem)
    {
      if (!isset($oem))
       $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('la_art_id')
            ->addAttributeToFilter($art_id_array);
      else
        $collection = Mage::getModel('catalog/product')->getCollection()       
            ->addAttributeToFilter($art_id_array)
            ->addAttributeToFilter('sku',array('like'=>'\_%'.$oem.'\_%'));
           // Limit the collection to 15 result
       $collection->setCurPage(15)->setPageSize(15);
       $collection->load();
       return $collection;
    }

  /**
    * get items by oem
   */
   public function getItemsByOem($oem,$type)
   {
    $i=0;
    $art_id_array[$i]=array('attribute'=>'category_id','eq'=>$type);
    $this->_itemCollection = $this->getItemsCollection($art_id_array,$oem);
    return $this->_itemCollection;
   }
   
   /**
    *Search analogs in external database (action)
   */
   public function AnalogAction(){
     $oem=$_POST['sku'];
     $gs_arr=$this->AnalogSearch($oem);
     //print_r($gs_arr);
     for ($i=0; $i<count($gs_arr);$i++)
      {
       $art_id_array[$i]=array('attribute'=>'gs_art_id','eq'=>$gs_arr[$i]['No']);
      }
      //print_r($art_id_array);
      $_items = $this->getItemsCollection($art_id_array,$oem);
      $arr_count=count($_items);     
      $string2='';
      $string='
              <ol class="mini-products-list">';
              foreach ($_items->getItems() as $_item): 
                $product = Mage::getModel('catalog/product')->load($_item->getId());
                $string2.='<li class="item">
                  <div class="product">
                      <a href="'.$_item->getProductUrl().'" title="'.$product->getName().
                      '" class="product-image" style="width:100px;"><img src="'.Mage::helper('catalog/image')->init($product, 'small_image')->resize(100).
                      '" width="100" height="100" alt="'.$product->getName().'" /></a>
                      <div class="product-details">
                          <p class="product-name" style="margin-left:12%;"><a href="'.$product->getProductUrl().'">'.$product->getName().'</a></p>';
                        $string2.='
                      </div>
                 </div>
                 </li>';
              endforeach;
        $string2.='</ol>';
    if ($arr_count==0) 
     echo "Аналоги не найдены!";
    else
     echo $string.$string2;
  }
  
  /**
    *Search analogs in external database 
   */
  public function AnalogSearch($oem)
  {
     $log = ''; // login
     $pass = ''; // password
     $user_cookie_file =dirname(__FILE__).'/cookie.txt'; // cookies 

     $url="";  //url for login
     $search_url=""; //url for search
     $json="";
     
     if (!file_exists($cookie_file)){
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL,$url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
       curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");
       curl_setopt($ch,CURLOPT_REFERER,$url);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
       curl_setopt($ch, CURLOPT_HEADER, array("Content-type: application/x-www-form-urlencoded")); 
       curl_setopt($ch, CURLOPT_POST, 1); 
       curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                                                  'UserName'=>$log,
                                                  'Password'=>$pass,
                                                  'RememberMe'=>'true'
                                                 ));
       curl_setopt($ch, CURLOPT_COOKIEJAR,  $user_cookie_file); 
       curl_setopt($ch, CURLOPT_COOKIEFILE, $user_cookie_file); 
       curl_setopt($ch, CURLOPT_AUTOREFERER,1);
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       $html = curl_exec($ch);
       curl_close($ch);
      }

     //$postvars={'No':'','No2':'CA1044', 'Location':"ЦЕНТР_WMS", 'PriceGroup':"РОЗН"};
     if (!file_exists($cookie_file)){
      $postvars=array(
       'No' => '',
       'No2' => $oem,
       'Location' => 'ЦЕНТР_WMS',
       'PriceGroup' => 'РОЗН'
       );

      $ch = curl_init();   
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_COOKIEFILE, $user_cookie_file); 
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postvars));
      curl_setopt($ch, CURLOPT_HEADER,0);  
      curl_setopt($ch, CURLOPT_AUTOREFERER,1);
      $arr = array();
      array_push($arr, 'Content-Type: application/json; charset=utf-8');
      curl_setopt($ch, CURLOPT_HTTPHEADER, $arr);
      curl_setopt($ch, CURLOPT_URL,$search_url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $html = curl_exec($ch);
      $json = json_decode($html, true);
      curl_close($ch);
    }
   
    return $json;
  }
   
}
