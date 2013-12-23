Unleaded All module
=====

Helper Methods:

**Attribute:** Mage::helper('ulall/attribute')
> 
> 		int|boolean getOptionIdByValue ( string $value, string $attribute_code [, int $store = 0 ] )
> 
> **Parameters:**  
> **value** - The label of the attribute option, if store id is specified will match against the store view label if found  
> 
> **attribute_code** - The attribute code to search for the option, must be of type [select, multiselect]  
> 
> **store** - The optional store id to search against, defaults to 0, or admin view  
>
> ================
> 
> 		boolean hasAttributeGroup ( Mage_Catalog_Model_Product $product, string $name )
> 
> **Parameters:**  
> **product** - A product object, used to determine the attribute set that the group will be search for in  
> 
> **name** - The attribute "group" (each "tab" shown in an attribute set) label. Case sensitive.  
>
> ================
> 
> 		array getGroupAttributes ( Mage_Catalog_Model_Product $product, string $name )
> 
> **Parameters:**  
> **product** - A product object, used to determine the attribute set the group will be searched for in  
> 
> **name** - The attribute group label whose containing attributes are to be returned  
>
> ================
> 
> 		array getSetAttributes ( string $name )
> 
> **Parameters:**  
> **name** - The attribute set from which to fetch the collection of attributes
>
> ================
> 
> 		Varien_Data_Collection getAttributeOptionsSorted ( string $attribute_code [, string $order = "ASC" )
> 
> **Parameters:**  
> **attribute_code** - The attribute code, must be one of type [select, multiselect]  
> 
> **order** - Optional sort order, one of ["ASC", "DESC"]

================

**Customer:** Mage::helper('ulall/customer')
> 
> 		array getAllGroups ( )
> 
> **Returns:**  
> 
> An associative array of groups, wherein the array key is the group id, the value is the group label
>
> ================
> 
> 		string getCurrentGroupName ( )
> 
> **Returns:**  
> 
> The label of the group that the current customer is associated with

================

**Product:** Mage::helper('ulall/product')
> 
> 		Mage_Catalog_Model_Product getParent ( int|Mage_Catalog_Model_Product $product )
> 
> **Parameters:**  
> **product** - A simple product id or object from which to find the parent (configurable) product
> 
> **Returns:**  
> 
> The parent configurable product if found, the original product object if not
>
> ================
> 
> 		Mage_Catalog_Model_Product|boolean getProductByOptions ( int|Mage_Catalog_Model_Product $product, array $options [, int $search = Unleaded_All_Helper_Product::SEARCH_BOTH [, boolean $strict = false ]] )
> 
> **Parameters:**  
> **product** - A configurable product id or object to search within for matching simples  
> 
> **options** - An associative array in which the key is the attribute_code and the value is the value to match against. If search parameter is 1 (value) the value of the array is matched against the value of the attribute, if search parameter is 2 (label), the value is matched against the getAttributeText value of the attribute.  
> 
> **search** - One of the possible search constants, [Unleaded_All_Helper_Product::SEARCH_BOTH, Unleaded_All_Helper_Product::SEARCH_LABEL, Unleaded_All_Helper_Product::SEARCH_VALUE]  
> 
> **strict** - If true, text matches are case sensitive  
> 
> **Returns:**  
> 
> The matching simple product on success, false on failure

================

**Category:** Mage::helper('ulall/category')
> 
> 		boolean isChildOf ( int|Mage_Catalog_Model_Category $child, int|Mage_Catalog_Model_Category $parent [, int $level = null [, int $store = null ]] )
> 
> **Parameters:**  
> **child** - The category to check if a subcategory of parent  
> 
> **parent** - The parent category to find the child in  
> 
> **level** - Optional number of "levels" between the parent and child. If difference is greater than the value, false is returned  
> 
> **store** - The optional store id that the categories must belong to  
> 
> **Returns:**  
> 
> true if the child is a subcategory of parent, false otherwise
>
> ================
> 
> 		boolean isDirectChildOf ( int|Mage_Catalog_Model_Category $child, int|Mage_Catalog_Model_Category $parent [, int $store = null ] )
> 
> **Parameters:**  
> **child** - The category to check if a subcategory of parent  
> 
> **parent** - The parent category to find the child in  
> 
> **store** - The optional store id that the categories must belong to  
> 
> **Returns:**  
> 
> true if the child is a direct subcategory of parent, false otherwise
>
> ================
> 
> 		boolean isSiblingOf ( int|Mage_Catalog_Model_Category $source, int|Mage_Catalog_Model_Category $target )
> 
> **Parameters:**  
> **source** - The first category to compare to target  
> 
> **target** - The second category to compare to source  
> 
> **Returns:**  
> 
> true if source and target are at the same level (depth) in the category tree, false otherwise
>
> ================
> 
> 		Unleaded_All_Helper_Category createCategory ( string $path [, int $active = 1 [, int $anchor = 0 [, int $store = null ]]] )
> 
> **Parameters:**  
> **path** - An xpath-like string that will be used to create a category tree. If a category already exists, nothing is changed, i.e. ("/Men's/Jackets/Winterwear/Gortex")  
> 
> **active** - Optional flag that makes each newly created category active or not  
> 
> **anchor** - Optional flag that makes each newly created category an anchor category  
> 
> **store** - Optional store id under which to create each category  
>
> ================
> 
> 		Mage_Catalog_Model_Category|boolean getCategoryByName ( string $name [, int $depth = 0 [, int $store = null ]] )
> 
> **Parameters:**  
> **name** - The category name to search for, case sensitive  
> 
> **depth** - Optional max depth in the category tree under which to search for the category, 0 = no max depth  
> 
> **store** - Optional store id under which to search for the category  
> 
> **Returns:**  
> 
> The found category on success, or false on failure