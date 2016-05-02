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
                'Stock' => $variation->checkStock(),
                'Title' => $variation->Title,
                'ProductID' => $variation->ProductID,
                'ID' => $variation->ID,
                'Price' => $variation->sellingPrice()
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
        $data = $this->owner->request->requestVar();
        $customerEmail = isset($data['email']) ? $data['email'] : null;
        $productID = isset($data['productid']) ? $data['productid'] : null;
        $productVariationID = isset($data['variationid']) ? $data['variationid'] : null;

        if (filter_var($customerEmail, FILTER_VALIDATE_EMAIL) && $productID > 0) {
            $notification = StockNotification::create();

            $product = Product::get()->byID($productID);
            if ($productVariationID > 0) {
                $product = ProductVariation::get()->byID($productVariationID);
                $notification->IsVariation = true;
                $notification->VariationID = $productVariationID;
            }

            if ($product && $product->exists()) {
                $notification->Email = $customerEmail;
                $notification->ProductID = $productID;
                $notification->ProductName = $product->Title;
                $notification->write();
            }
        }

        return $this->owner->redirectBack();
    }

    public function wineoutofstock()
    {
        $data = $this->owner->request->requestVar();
        $customerEmail = isset($data['email']) ? $data['email'] : null;
        $productID = isset($data['productid']) ? $data['productid'] : null;

        if (filter_var($customerEmail, FILTER_VALIDATE_EMAIL) && $productID > 0) {
            $product = WineProduct::get()->byID($productID);
            if ($product && $product->exists()) {
                $config = ShopStore::current();
                $messageConfig = MessageConfig::current_message_config();
                $mailListID = $config->VintageReleaseList;
                $apikey = $messageConfig->MailChimpAPIKey;

                $api = new MCAPI($apikey);

                if ($api && $response = $api->listSubscribe(
                        $mailListID,
                        $customerEmail,
                        $merge_vars = null,
                        $email_type = 'html',
                        $messageConfig->DoubleOptin,
                        $update_existing = false,
                        $replace_interests = true,
                        $messageConfig->SendWelcomeMail)
                ) {
                    $segID = $product->SegmentID;

                    if (empty($segID)) {
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
        }

        return $this->owner->redirectBack();
    }

    public function notifyNewRelease()
    {
        $data = $this->owner->request->requestVar();
        $customerEmail = isset($data['email']) ? $data['email'] : null;

        if (filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $messageConfig = MessageConfig::current_message_config();
            $mailList = NewsletterList::get()->filter(array('Title' => 'New Release List'));
            $apikey = $messageConfig->MailChimpAPIKey;
            $api = new MCAPI($apikey);

            if ($api && $response = $api->listSubscribe(
                    $mailList,
                    $customerEmail,
                    $merge_vars = null,
                    $email_type = 'html',
                    $messageConfig->DoubleOptin,
                    $update_existing = false,
                    $replace_interests = true,
                    $messageConfig->SendWelcomeMail)
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