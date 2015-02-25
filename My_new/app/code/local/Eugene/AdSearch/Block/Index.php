<?php   
class Eugene_AdSearch_Block_Index extends Mage_Core_Block_Template{   
 public function getAllManufacturers(){
   $read = Mage::getSingleton('core/resource')->getConnection('core_read');
   $sql = 'select * from mg_manufacturers order by MFA_BRANDS';
   $result = $read->query($sql);
   $string='<option value="">Выберите марку</option>'; 

   if (!$result) {
    return $string;
   }

   $row = $result->fetch(PDO::FETCH_ASSOC);
   while ($row = $result->fetch(PDO::FETCH_ASSOC)){
    $string= $string.'<option value="'.$row['MFA_ID'].'">'.$row['MFA_BRANDS'].'</option>';
   }
 }
}
