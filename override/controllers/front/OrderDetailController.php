<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderDetailController extends OrderDetailControllerCore
{
 
    public function postProcess()
    {
            if (Tools::isSubmit('submitMessage'))
		{
			$idOrder = (int)(Tools::getValue('id_order'));
			$msgText = Tools::getValue('msgText');

			if (!$idOrder || !Validate::isUnsignedId($idOrder))
				$this->errors[] = Tools::displayError('The order is no longer valid.');
			elseif (empty($msgText))
				$this->errors[] = Tools::displayError('The message cannot be blank.');
			elseif (!Validate::isMessage($msgText))
				$this->errors[] = Tools::displayError('This message is invalid (HTML is not allowed).');
			if (!count($this->errors))
			{
				$order = new Order($idOrder);
				if (Validate::isLoadedObject($order) && $order->id_customer == $this->context->customer->id)
				{
					//check if a thread already exist
					$id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($this->context->customer->email, $order->id);

                                        $id_product = (int)Tools::getValue('id_product');
					$cm = new CustomerMessage();
					if (!$id_customer_thread)
					{
						$ct = new CustomerThread();
						$ct->id_contact = 0;
						$ct->id_customer = (int)$order->id_customer;
						$ct->id_shop = (int)$this->context->shop->id;
                                                if (($id_product) && $order->orderContainProduct((int)$id_product))
							$ct->id_product = $id_product;
						$ct->id_order = (int)$order->id;
						$ct->id_lang = (int)$this->context->language->id;
						$ct->email = $this->context->customer->email;
						$ct->status = 'open';
						$ct->token = Tools::passwdGen(12);
						$ct->add();
					}
					else
						$ct = new CustomerThread((int)$id_customer_thread);
					$cm->id_customer_thread = $ct->id;
					if (($id_product) && $order->orderContainProduct((int)$id_product))
                                            $cm->id_product = $id_product;
					$cm->message = $msgText;
					$cm->ip_address = ip2long($_SERVER['REMOTE_ADDR']);
					$cm->add();

					if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE'))
						$to = strval(Configuration::get('PS_SHOP_EMAIL'));
					else
					{
						$to = new Contact((int)(Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
						$to = strval($to->email);
					}
					$toName = strval(Configuration::get('PS_SHOP_NAME'));
					$customer = $this->context->customer;
                                        $product = new Product($id_product);
					$product_name = '';
					if (Validate::isLoadedObject($product) && isset($product->name[(int)$this->context->language->id]))
					$product_name = $product->name[(int)$this->context->language->id];

					if (Validate::isLoadedObject($customer))
						Mail::Send($this->context->language->id, 'order_customer_comment', Mail::l('Message from a customer'),
						array(
							'{lastname}' => $customer->lastname,
							'{firstname}' => $customer->firstname,
							'{email}' => $customer->email,
							'{id_order}' => (int)($order->id),
							'{order_name}' => $order->getUniqReference(),
							'{message}' => Tools::nl2br($msgText),
							'{product_name}' => $product_name
						),
						$to, $toName, $customer->email, $customer->firstname.' '.$customer->lastname);

					if (Tools::getValue('ajax') != 'true')
						Tools::redirect('index.php?controller=order-detail&id_order='.(int)$idOrder);

					$this->context->smarty->assign('message_confirmation', true);
				}
				else
					$this->errors[] = Tools::displayError('Order not found');
			}
		} 
        if (Tools::isSubmit('markAsReceived'))
        {
            $idOrder = (int)(Tools::getValue('id_order'));
            $order = new Order($idOrder);
 
            if(Validate::isLoadedObject($order))
            {
                if($order->getCurrentState() == 15) // if the order is shipped
                {
                    $new_history = new OrderHistory();
                    $new_history->id_order = (int)$order->id;
                    $new_history->changeIdOrderState(3, $order); // 16: Ready for Production
                    //var_dump($order,$new_history);
                    $myfile = fopen(PS_PRODUCT_IMG_PATH."/orders/".$order->reference.".txt", "w") or die("Unable to open file!");
                    $txt = "Order Confirmed\n Order Reference: ".$order->reference;
                    fwrite($myfile, $txt);
                    fclose($myfile);
                    $new_history->addWithemail(true);    
                }
 
                $this->context->smarty->assign('receipt_confirmation', true);
 
            } else $this->_errors[] = Tools::displayError('Error: Invalid order number');
             
        }       
 
    }
    
    public function initContent()
	{
		parent::initContent();

		if (!($id_order = (int)Tools::getValue('id_order')) || !Validate::isUnsignedId($id_order))
			$this->errors[] = Tools::displayError('Order ID required');
		else
		{
			$order = new Order($id_order);
			if (Validate::isLoadedObject($order) && $order->id_customer == $this->context->customer->id)
			{
				$id_order_state = (int)($order->getCurrentState());
				$carrier = new Carrier((int)($order->id_carrier), (int)($order->id_lang));
				$addressInvoice = new Address((int)($order->id_address_invoice));
				$addressDelivery = new Address((int)($order->id_address_delivery));

				$inv_adr_fields = AddressFormat::getOrderedAddressFields($addressInvoice->id_country);
				$dlv_adr_fields = AddressFormat::getOrderedAddressFields($addressDelivery->id_country);

				$invoiceAddressFormatedValues = AddressFormat::getFormattedAddressFieldsValues($addressInvoice, $inv_adr_fields);
				$deliveryAddressFormatedValues = AddressFormat::getFormattedAddressFieldsValues($addressDelivery, $dlv_adr_fields);

				if ($order->total_discounts > 0)
					$this->context->smarty->assign('total_old', (float)($order->total_paid - $order->total_discounts));
				$products = $order->getProducts();

				/* DEPRECATED: customizedDatas @since 1.5 */
				$customizedDatas = Product::getAllCustomizedDatas((int)($order->id_cart));
				Product::addCustomizationPrice($products, $customizedDatas);

				OrderReturn::addReturnedQuantity($products, $order->id);

				$customer = new Customer($order->id_customer);
                                $messages = CustomerMessage::getMessagesByOrderId((int)($order->id), false);
                                $count=0;
                                foreach ($messages as $message){
                                    $product_message = new Product($message['id_product']);
                                    if (Validate::isLoadedObject($product_message) && isset($product_message->name[(int)$this->context->language->id]))
                                    $message['product_name'] = $product_message->name[(int)$this->context->language->id];
                                    else
                                    $message['product_name']='-';
                                    $messages[$count++]=$message;
                                }
                                $this->context->smarty->assign(array(
					'shop_name' => strval(Configuration::get('PS_SHOP_NAME')),
					'order' => $order,
					'return_allowed' => (int)$order->isReturnable(),
					'currency' => new Currency($order->id_currency),
					'order_state' => (int)$id_order_state,
					'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
					'invoice' => (OrderState::invoiceAvailable($id_order_state) && count($order->getInvoicesCollection())),
					'order_history' => $order->getHistory($this->context->language->id, false, true),
					'products' => $products,
					'discounts' => $order->getCartRules(),
					'carrier' => $carrier,
					'address_invoice' => $addressInvoice,
					'invoiceState' => (Validate::isLoadedObject($addressInvoice) && $addressInvoice->id_state) ? new State($addressInvoice->id_state) : false,
					'address_delivery' => $addressDelivery,
					'inv_adr_fields' => $inv_adr_fields,
					'dlv_adr_fields' => $dlv_adr_fields,
					'invoiceAddressFormatedValues' => $invoiceAddressFormatedValues,
					'deliveryAddressFormatedValues' => $deliveryAddressFormatedValues,
					'deliveryState' => (Validate::isLoadedObject($addressDelivery) && $addressDelivery->id_state) ? new State($addressDelivery->id_state) : false,
					'is_guest' => false,
					'messages' => $messages,
					'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
					'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
					'isRecyclable' => Configuration::get('PS_RECYCLABLE_PACK'),
					'use_tax' => Configuration::get('PS_TAX'),
					'group_use_tax' => (Group::getPriceDisplayMethod($customer->id_default_group) == PS_TAX_INC),
					/* DEPRECATED: customizedDatas @since 1.5 */
					'customizedDatas' => $customizedDatas,
					/* DEPRECATED: customizedDatas @since 1.5 */
					'reorderingAllowed' => !(int)(Configuration::get('PS_DISALLOW_HISTORY_REORDERING'))
				));

				if ($carrier->url && $order->shipping_number)
					$this->context->smarty->assign('followup', str_replace('@', $order->shipping_number, $carrier->url));
				$this->context->smarty->assign('HOOK_ORDERDETAILDISPLAYED', Hook::exec('displayOrderDetail', array('order' => $order)));
				Hook::exec('actionOrderDetail', array('carrier' => $carrier, 'order' => $order));

				unset($carrier, $addressInvoice, $addressDelivery);
			}
			else
				$this->errors[] = Tools::displayError('This order cannot be found.');
			unset($order);
		}

		$this->setTemplate(_PS_THEME_DIR_.'order-detail.tpl');
	}

	public function setMedia()
	{
		if (Tools::getValue('ajax') != 'true')
		{
			parent::setMedia();
			$this->addCSS(_THEME_CSS_DIR_.'history.css');
			$this->addCSS(_THEME_CSS_DIR_.'addresses.css');
		}
	}
 
 
}