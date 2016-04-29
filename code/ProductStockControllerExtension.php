<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 29/04/2016
 * Time: 4:50 PM
 */
class ProductStockControllerExtension extends Extension
{
    private static $allowed_actions = array(
        'variationstock',
        'outofstock',
        'wineoutofstock',
        'notifyNewRelease',
    );

    public function variationstock()
    {
        $variation = $this->owner->getVariationByAttributes($_GET['ProductAttributes']);

        if ($variation) {
            return $this->owner->jsonResponse(array(
                'Status' => 1,
                'Stock' => $variation->StockAmount,
                'Title' => $variation->Title,
                'ProductID' => $variation->ProductID,
                'ID' => $variation->ID,
                'Price' => $variation->Price
            ));
        } else {
            return $this->owner->jsonResponse(array(
                'Status' => 0,
                'Message' => 'There is no available product for those options'
            ));
        }
    }

    public function outofstock()
    {
        if (!empty($_POST['email'])) {
            $customerEmail = $_POST['email'];
            $productID = $_POST['productid'];
            $ProductVariation = $_POST['variationid'];

            $notification = new StockNotification();
            $notification->Email = $customerEmail;
            if (!empty($ProductVariation) && !empty($productID)) {
                $product = ProductVariation::get()->filter(array(
                    'ProductID' => $productID,
                    'ID' => $ProductVariation
                ))->first();
                if (!empty($product)) {
                    $notification->ProductID = $productID;
                    $notification->ProductName = $product->Title;
                    $notification->VariationID = $ProductVariation;
                }
            } elseif (!empty($productID)) {
                $product = Product::get()->filter(array('ID' => $productID))->first();
                if (!empty($product)) {
                    $notification->ProductID = $productID;
                    $notification->ProductName = $product->Title;
                }
            }

            $notification->write();
        }

        return $this->owner->redirectBack();
    }

    public function wineoutofstock()
    {
        if (!empty($_POST['email'])) {
            $customerEmail = $_POST['email'];
            $productID = $_POST['productid'];

            $config = SiteConfig::current_site_config();
            $messageConfig = MessageConfig::current_message_config();
            $mailListID = $config->VintageReleaseList;
            $apikey = $messageConfig->MailChimpAPIKey;
            $api = new MCAPI($apikey);

            if ($api && $response = $api->listSubscribe(
                    $mailListID,
                    $customerEmail,
                    $merge_vars = null,
                    $email_type = 'html',
                    MessageConfig::current_message_config()->DoubleOptin,
                    $update_existing = false,
                    $replace_interests = true,
                    MessageConfig::current_message_config()->SendWelcomeMail)
            ) {
                $product = WineProduct::get()->byID($productID);
                $segID = $product->SegmentID;

                if (empty($segID)) {
                    //create signment
                    $params = array();
                    $params["apikey"] = $apikey;
                    $params["id"] = $mailListID;
                    $params["name"] = $product->Title;
                    $segID = $api->callServer("listStaticSegmentAdd", $params);
                    $product->updateFieldsRaw(array('SegmentID' => $segID));
                }

                $params = array();
                $params["apikey"] = $apikey;
                $params["id"] = $mailListID;
                $params["seg_id"] = $segID;
                $params["batch"] = array($customerEmail);
                $api->callServer("listStaticSegmentMembersAdd ", $params);
            }
        }

        return $this->owner->redirectBack();
    }

    public function notifyNewRelease()
    {
        if (!empty($_POST['email'])) {
            $customerEmail = $_POST['email'];
            $messageConfig = MessageConfig::current_message_config();
            $mailList = NewsletterList::get()->filter(array('Title' => 'New Release List'));
            $apikey = $messageConfig->MailChimpAPIKey;
            $api = new MCAPI($apikey);

            if ($api && $response = $api->listSubscribe($mailList, $customerEmail, $merge_vars = null,
                    $email_type = 'html', MessageConfig::current_message_config()->DoubleOptin,
                    $update_existing = false, $replace_interests = true,
                    MessageConfig::current_message_config()->SendWelcomeMail)
            ) {
                $result = array(
                    'status' => 1,
                    'message' => 'Thank you for your interest in our new vintage releases'
                );
            } else {
                $result = array(
                    'status' => 0,
                    'message' => 'Something went wrong'
                );
            }

            return $result;
        }
    }
}