<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    
    public function instanciaPaypay(){

        // return "<script data-sdk-integration-source=\"integrationbuilder_sc\" src=\"https://www.paypal.com/sdk/js?client-id=".env('PAYPAL_CLIENT_ID')."&components=buttons\"></script>";
        return "https://www.paypal.com/sdk/js?client-id=".env('PAYPAL_CLIENT_ID')."&components=buttons";

    }

    public function obtenerBotonPaypal($mount,$servicio,$destino){

        return $botonPaypal = "const FUNDING_SOURCES = [
                                paypal.FUNDING.PAYPAL,                
                            ];
                var actionStatus;            

                FUNDING_SOURCES.forEach(fundingSource => {
                  paypal.Buttons({
                    fundingSource,

                    style: {
                      layout: 'vertical',
                      shape: 'rect',
                      color: (fundingSource == paypal.FUNDING.PAYLATER) ? 'gold' : '',
                    },                                       
                    createOrder: async (data, actions) => {
                        
                        // alert('NO');
                        // return false;

                        try {                                                    
                            const response = await fetch(\"".url('api/createorder')."\", {
                              method: \"POST\",
                              headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                              },
                              body: JSON.stringify({
                                mount: \"".$mount."\", 
                                b: 'Textual content'
                              })
                            });

                            const details = await response.json();
                            console.log(details);
                            return details.id;
                        }
                        catch (error) {
                            // Handle the error or display an appropriate error message to the user
                            console.error(error);            
                        }
                    },
                    onInit: function(data, actions) {
                      // Disable the buttons
                        console.log('Disable');
                        actions.disable(); 
                        actionStatus = actions;                     
                      // Listen for changes                                                 
                        
                        document.querySelectorAll('#form-reservation-send input').forEach(i=>{                          
                          i.addEventListener('change',()=>{
                            var bValidate = false;
                            document.querySelectorAll('#form-reservation-send input').forEach(text=>{
                              if(text.value == ''){
                                bValidate = true;
                              }
                            });
                            
                            if (!bValidate) {
                              actions.enable();
                            } else {
                              actions.disable();
                            }
                          });
                        });                                                
                    },                            
                    onApprove: async (data, actions) => {                                        
                      try {
                        
                        console.log(data);                                                
                        const form = document.getElementById('form-reservation-send');

                        //Get all form elements
                        const formElements = Array.from(form.elements);

                        var columns = {};
                        var objarray = [];                        
                        formElements.forEach(element => {
                            // columns = {
                            //     name: element.name,
                            //     value: element.value
                            // };
                            // objarray.push(columns);
                            columns[element.name] = element.value;
                        });
                        data[\"data\"] = columns;                        

                        const response = await fetch(\"".url('api/confirmar')."\", {
                          method: \"POST\",
                          headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                          },                          
                          body: JSON.stringify(data) //JSON.stringify({a: 1, b: 'Textual content'})
                        });

                        const details = await response.json();
                        
                        // if(details.btrue){   
                        //   // window.location.href = 'https://altripscancun.com/Reservation';  
                        //   navigate('/Reservation', {
                        //       state: {
                        //           cJSON: details,
                        //       }                
                        //   });                        
                        // }

                        if(details.bError){

                          alert(details.cMensagge);
                          return false;

                        }

                        // Three cases to handle:
                        //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                        //   (2) Other non-recoverable errors -> Show a failure message
                        //   (3) Successful transaction -> Show confirmation or thank you message

                        // This example reads a v2/checkout/orders capture response, propagated from the server
                        // You could use a different API or structure for your 'orderData'
                        const errorDetail = Array.isArray(details.details) && details.details[0];

                        if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                          return actions.restart();
                          // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                        }

                        if (errorDetail) {
                          let msg = 'Sorry, your transaction could not be processed.';
                          msg += errorDetail.description ? ' ' + errorDetail.description : '';
                          msg += details.debug_id ? ' (' + details.debug_id + ')' : '';
                          alert(msg);
                        }

                        // Successful capture! For demo purposes:
                        //console.log('Capture result', details, JSON.stringify(details, null, 2));
                        const transaction = details.purchase_units[0].payments.captures[0];
                        //alert('Transaction ' + transaction.status + ': ' + transaction.id + 'See console for all available details');
                      } catch (error) {
                        console.error(error);
                        // Handle the error or display an appropriate error message to the user
                      }
                    }
                  }).render(\"#paypal-button-container\");
                })";
    }
}
