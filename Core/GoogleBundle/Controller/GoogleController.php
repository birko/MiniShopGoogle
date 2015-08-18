<?php

namespace Core\GoogleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Site\ShopBundle\Controller\ShopController;
use Core\GoogleBundle\Entity\GoogleHelper;

class GoogleController extends ShopController
{
    public function exportAction()
    {
        $request = $this->getRequest();
        
        $minishop  = $this->container->getParameter('minishop');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository("CoreProductBundle:Product")->findByCategoryQuery(null, false, true,false, false);
        if ($request->get('_locale')) {
            $query->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                $request->get('_locale') // take locale from session or request etc.
            );
        }

        $medias = $em->getRepository("CoreProductBundle:ProductMedia")->getProductsMediasArray(null, array('image'), $request->get('_locale'));
        $videos = $em->getRepository("CoreProductBundle:ProductMedia")->getProductsMediasArray(null, array('video'), $request->get('_locale'));
        $stocks = $em->getRepository("CoreProductBundle:Stock")->getStocksArray(null, $request->get('_locale'));
        $prices = $em->getRepository("CoreProductBundle:Price")->getPricesArray();
        $categories = $em->getRepository("CoreProductBundle:ProductCategory")->getCategoriesArray(null, $request->get('_locale'));
        $attributes = $em->getRepository("CoreProductBundle:Attribute")->getGroupedAttributesByProducts(array(), array(), $request->get('_locale'));
        $options = $em->getRepository("CoreProductBundle:ProductOption")->getGroupedOptionsByProducts(array(), array(), $request->get('_locale'));
        $variations = $em->getRepository("CoreProductBundle:ProductVariation")->getGroupedVariationsByProducts(array(), $request->get('_locale'));
        $shippings = $em->getRepository("CoreShopBundle:Shipping")->getShippingQueryBuilder(null, true)->getQuery()->getResult();
        $googleProducts = $em->getRepository("CoreGoogleBundle:ProductCategory")->getGoogleCategoriesArray();
        $googleCategories = $em->getRepository("CoreGoogleBundle:CategoryCategory")->getGoogleCategoriesArray();;
        $helper = & GoogleHelper::getCategories($request->get('_locale'));

        $pricegroup_id = $request->get('pricegroup');
        $priceGroup = null;
        if ($pricegroup_id !== null) {
            $priceGroup = $em->getRepository('CoreUserBundle:PriceGroup')->find($pricegroup_id);
        }
        $priceGroup = ($priceGroup) ? $priceGroup : $this->getPriceGroup();
        $currency_id = $request->get('currency');
        $currency = null;
        if ($currency_id !== null) {
            $currency = $em->getRepository('CorePriceBundle:Currency')->find($currency_id);
        }
        $currency = ($currency) ? $currency : $this->getCurrency();
        
        $pricetypes = $this->container->hasParameter('google.prices') ? $this->container->getParameter('google.prices'): array('normal');
        $delivery_id = $this->container->hasParameter('google.delivery_id') ? $this->container->getParameter('google.delivery_id') : array();
        
        $request= $this->getRequest();
        $document = new \DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;
        $rss = $document->appendChild($document->createElement('rss'));
        $rssversion = $document->createAttribute('version');
        $rssversion->value = "2.0";
        $rss->appendChild($rssversion);
        $rssns = $document->createAttribute('xmlns:g');
        $rssns->value = "http://base.google.com/ns/1.0";
        $rss->appendChild($rssns);
        $shop = $document->createelement('channel');
        $rss->appendChild($shop);
        $paths = array();
        //info
        $name = $document->createElement('title');
        $name->appendChild($document->createCDATASection($pname));
        $shop->appendChild($name);
        
        $url = $document->createElement('link');
        $routeParams = array();
        if ($request->get('_locale')) {
            $routeParams['_locale'] = $request->get('_locale');
        }
        $url->appendChild($document->createTextNode($this->generateUrl('category_homepage', $routeParams, true)));
        $shop->appendChild($url);

        $description = str_replace(array("\x0B", "\0", "\r", "\t"), ' ', strip_tags()); // zrusenie niektorych whitespacesnakov za medzery
        $description = preg_replace('/\s+/', ' ', $description);
        $description = str_replace(array('&nbsp;', '&amp;'), array(" ", "&"), $description);
        $desc = $document->createElement('description');
        if (!empty($description)) {
            $desc->appendChild($document->createCDATASection($description));
        }
        $shop->appendChild($desc);
        //items
        foreach ($query->getResult()  as $product) {
            $item = $document->createElement('item');

            $code = $document->createElement('g:id');
            $code->appendChild($document->createTextNode($product->getId()));
            $item->appendChild($code);
            
            $name = $document->createElement('title');
            $name->appendChild($document->createCDATASection($pname));
            $item->appendChild($name);
            
            $url = $document->createElement('link');
            $routeParams = array('slug'=> $product->getSlug());
            if ($request->get('_locale')) {
                $routeParams['_locale'] = $request->get('_locale');
            }
            $url->appendChild($document->createTextNode($this->generateUrl('product_site', $routeParams, true)));
            $item->appendChild($url);

            $description = str_replace(array("\x0B", "\0", "\r", "\t"), ' ', strip_tags($product->getLongDescription() . " " . $product->getLongDescription())); // zrusenie niektorych whitespacesnakov za medzery
            $description = preg_replace('/\s+/', ' ', $description);
            $description = str_replace(array('&nbsp;', '&amp;'), array(" ", "&"), $description);
            $desc = $document->createElement('description');
            if (!empty($description)) {
                $desc->appendChild($document->createCDATASection($description));
            }
            $item->appendChild($desc);
            
            $code = $document->createElement('g:id');
            $code->appendChild($document->createTextNode($product->getId()));
            $item->appendChild($code);
            
            $status = $document->createElement('g:condition');
            $status->appendChild($document->createTextNode("new"));
            $item->appendChild($status);
            
            $cat = $document->createElement('g:google_product_category');
            $pom = "";
            if(
                isset($googleProducts[$product->getId()]) &&
                isset($googleProducts[$product->getId()]['google']) &&
                !empty($googleProducts[$product->getId()]['google'])
            ) {
                $pom = $helper[$googleProducts[$product->getId()]['google']];
            } else {
                if (isset($categories[$product->getId()]) && $categories[$product->getId()]->getProductCategories()->count() > 0) {
                    $category  = $categories[$product->getId()]->getProductCategories()->first()->getCategory();
                    $category_id = $category->getId();
                    if(
                        isset($googleCategories[$category_id]) &&
                        isset($googleCategories[$category_id]['google']) &&
                        !empty($googleCategories[$category_id]['google'])
                    ) {
                        $pom = $helper[$googleCategories[$category_id]['google']];
                    } else {
                        if (isset($paths[$category_id])) {
                            $pom = $paths[$category_id];
                        } else {
                            $path ="";
                            $categoryquery = $em->getRepository('CoreCategoryBundle:Category')
                            ->getPathQueryBuilder($category)
                            ->andWhere("node.enabled=:enabled")
                            ->setParameter("enabled", true)
                            ->getQuery();
                            if ($request->get('_locale')) {
                                $categoryquery->setHint(
                                    \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 
                                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                                );
                                $categoryquery->setHint(
                                    \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                                    $request->get('_locale') // take locale from session or request etc.
                                );
                            }
                            $pathcategories = $categoryquery->getResult();
                            if (!empty($pathcategories)) {
                                foreach ($pathcategories as $pathcat) {
                                    if (!empty($path)) {
                                        $path .= " | ";
                                    }
                                    $path .= $pathcat->getTitle();
                                }
                            }
                            $pom = $path;
                            $paths[$category_id] = $path;
                        }
                    }
                }
            }
            $cat->appendChild($document->createTextNode($pom));
            $item->appendChild($cat);
            
            $price = $document->createElement('g:price');
            $pprice = 0;
            if (isset($prices[$product->getId()])) {
                foreach($pricetypes as $type) {
                    $priceEntity = $prices[$product->getId()]->getMinimalPrice($currency, $priceGroup, $type);
                    if ($priceEntity) {
                        $pprice = $priceEntity->getPriceVat();
                        break;
                    }
                }
            }
            $price->appendChild($document->createTextNode(number_format($pprice, 2, '.', '')));
            $item->appendChild($price);
            
            $price = $document->createElement('g:currency');
            $price->appendChild($document->createTextNode($currency->getCode()));
            $item->appendChild($price);
            
            if (isset($medias[$product->getId()])) {
                $imgmedia = reset($media[$product->getId()]);
                if(!empty($imgmedia)) {
                    $img = $document->createElement("g:image_link");
                    $img->appendChild($document->createTextNode($request->getScheme() . '://' . $request->getHttpHost() . '/'. $imgmedia->getWebPath('original')));
                    $item->appendChild($img);
                }
            }

            $manuf = $document->createElement('g:brand');
            if ($product->getVendor()) {
                $manuf->appendChild($document->createCDATASection(trim($product->getVendor()->getTitle())));
            }
            $item->appendChild($manuf);

            $avb = $document->createElement('g:availability');
            $qdocument = "in stock";
            if (isset($stocks[$product->getId()])) {
                $stock = reset($stocks[$product->getId()]);
                $qdocument = ($stock->getAmount() > 0 || ($stock->getAvailability())) ? "in stock" : "out of stock";
            }
            $avb->appendChild($document->createTextNode($qdocument));
            $item->appendChild($avb);
            
            if (!empty($shippings)) {
                foreach($shippings as $ship) {
                    $is = $document->createElement('g:shipping');
                    $sh = $document->createElement('service');
                    if (array_key_exists($ship->getId(), $delivery_id)){
                        $sh->appendChild($document->createTextNode($delivery_id[$ship->getId()]));
                    } else {
                         $sh->appendChild($document->createTextNode($ship->getName()));
                    }
                    $is->appendChild($sh);
                    $sh = $document->createElement('g:price');
                    $sh->appendChild($document->createTextNode(number_format($ship->calculatePriceVAT($currency), 2, '.', '') . $currency->getCode()));
                    $is->appendChild($sh);
                    $sh = $document->createElement('g:country');
                    $sh->appendChild($document->createTextNode($ship->getState()->getCode()));
                    $is->appendChild($sh);
                    $item->appendChild($is);
                }
            }
            /*
            $parameters = array();
            if (isset($variations[$product->getId()])) {
                foreach ($variations[$product->getId()] as $key => $variation) {
                    $parameters[$variations] = array();
                    foreach ($variation as $attribute_name => $value) {
                        $parameters[$variations][$attribute_name][$value['value']] = array(
                            'name' => $attribute_name,
                            'value' => $value['value'],
                        );
                    }
                    $keys = array_keys($variation);
                    if (isset($attributes[$product->getId()])) {
                        foreach ($attributes[$product->getId()] as $attribute_name => $values) {
                            if (!in_array($attribute_name, $keys)) {
                                foreach ($values as $av) {
                                    $parameters[$variations][$attribute_name][$av['value']] = array(
                                        'name' => $attribute_name,
                                        'value' => $av['value'],
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                foreach(array($attributes, $options) as $parameterArray)
                {
                    if(isset($parameterArray[$product->getId()])) {
                        foreach($parameterArray[$product->getId()] as $aname => $avalues) {
                            foreach($avalues as $av) {
                                $parameters[$aname][$av['value']] = array(
                                    'name' => $aname,
                                    'value' => $av['value'],
                                );
                            }
                        }
                    }
                }
            }
            if(!empty($parameters)) {
                $combinations = $this->addCombination($parameters);
                foreach($combinations as $comb) {
                    $clone = $item->cloneNode(true);
                    foreach ($comb as $cname => $cvalue) {
                        $param = $document->createElement('PARAM');
                        $param_name = $document->createElement('PARAM_NAME');
                        $param_name->appendChild($document->createTextNode($cname));
                        $param->appendChild($param_name);
                        $param_val = $document->createElement('VAL');
                        $param_val->appendChild($document->createTextNode($cvalue));
                        $param->appendChild($param_val);
                        $clone->appendChild($param);
                    }
                    $itemgroup = $document->createElement('g:item_group_id');
                    $icode = trim($product->getId());
                    $itemgroup->appendChild($document->createTextNode($icode));
                    $clone->appendChild($itemgroup);
                    $shop->appendChild($clone);
                }
            } else {
                $shop->appendChild($item);
            }
            */
            $shop->appendChild($item);            
        }

        $response = new Response();
        $response->setContent($document->saveXML());
        $response->headers->set('Content-Encoding', ' UTF-8');
        $response->headers->set('Content-Type', ' text/xml; charset=UTF-8');
        $response->headers->set('Content-disposition', ' attachment;filename=google.xml');

        return $response;
    }
    
    private function addCombination($options)
    {
        $comb = array_shift($options);
        $first = reset($comb);
        $option_name = $first['name'];
        $result = array();
        foreach ($comb as $ovalues) {
            $option_value = $ovalues['value'];
            if(!empty($options)) {
                $prev_result = $this->addCombination($options);
                foreach($prev_result as $pom) {
                    $result[] = array_merge(array($option_name => $option_value), $pom);
                }
            } else {
                $result[] =  array($option_name => $option_value);
            }
        }
        
        return $result;
    }
    
    public function parseSekcieAction()
    {
        $sections = array(
            'sk' => 'http://www.google.com/basepages/producttype/taxonomy-with-ids.cs-CZ.txt',
            'cs' => 'http://www.google.com/basepages/producttype/taxonomy-with-ids.cs-CZ.txt',
            'de' => 'http://www.google.com/basepages/producttype/taxonomy-with-ids.de-DE.txt',
        );
        $file = "<?php\narray(\n";
        foreach($sections as $lang => $url) 
        {
            $data  = file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $file .= "\t\"{$lang}\" => array(\n";
           
            foreach ($data as $key => $line) {
                if ($key > 0) {
                    $split = explode("-", $line);
                    $cat_id = trim($split[0]);
                    $cat_path = trim($split[1]);
                    $file .=  "\t\t\"{$cat_id}\" => \"{$cat_path}\",\n";
                }
            }
            $file .= "\t),\n";
        }
        $file .= ");\n";
        
        echo $file;
        $response = new Response();
        $response->setContent($file);
        $response->headers->set('Content-Encoding', ' UTF-8');
        $response->headers->set('Content-Type', ' text/xml; charset=UTF-8');
        $response->headers->set('Content-disposition', ' attachment;filename=google.php');

        return $response;
    }
}
