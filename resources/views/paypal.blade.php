<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PayPal JS SDK Demo</title>
</head>

<body>
  <div id="paypal-button-container"></div>

  <div>
    
  </div>
  
  <script data-sdk-integration-source="integrationbuilder_sc" src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID')}}&components=buttons"></script>
  
  <script>
      
    


        // fetch('https://api.altripscancun.com/api/get', {
        fetch('{{url('api/createtrip')}}', {
           method: "POST", 
           headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
           },                      
           body: JSON.stringify({
              
              "type_trip":"1",
              "hotel_id":"1127",
              "passengers":"5",
              "arrival_date":"10/11/2023",
              "departure_date":"10-12-2023"
           })
        })
        .then(response => response.json())
        .then(text => {
          
          // var ins = document.createElement('script');
          // ins.type = 'text/javascript';
          // ins.setAttribute('data-sdk-integration-source',"integrationbuilder_sc");        
          // ins.setAttribute('src',text.instance);          
          // ins.appendChild(document.createTextNode(text.bton_paypal));
          // document.body.appendChild(ins);

          var s = document.createElement('script');
          s.type = 'text/javascript';
          var code = 'alert("hello world!");';        
          // s.appendChild(document.createTextNode(text.bton_paypal));          
          s.textContent = text.bton_paypal;        
          document.body.appendChild(s);
          console.log(text)

        })
        .catch(error => console.log(error));
      

                  // const FUNDING_SOURCES = [ 
                  //                 paypal.FUNDING.PAYPAL,                
                  //             ];

                  // FUNDING_SOURCES.forEach(fundingSource => {
                  //   paypal.Buttons({
                  //     fundingSource,

                  //     style: {
                  //       layout: 'vertical',
                  //       shape: 'rect',
                  //       color: (fundingSource == paypal.FUNDING.PAYLATER) ? 'gold' : '',
                  //     },
                  //     createOrder: async (data, actions) => {
                  //       try {
                  //         const response = await fetch("{{url('api/createorder')}}", {
                  //           method: "POST",
                  //           headers: {
                  //             'Accept': 'application/json',
                  //             'Content-Type': 'application/json'
                  //           },
                  //           body: JSON.stringify({mount: "2200.00", b: 'Textual content'})
                  //         });

                  //         const details = await response.json();
                  //         return details.id;
                  //       } catch (error) {
                  //         // Handle the error or display an appropriate error message to the user
                  //         console.error(error);            
                  //       }
                  //     },        
                  //     onApprove: async (data, actions) => {
                  //       try {
                  //         const response = await fetch(`{{url('api/createorder')}}`, {
                  //           method: "POST",
                  //           headers: {
                  //             'Accept': 'application/json',
                  //             'Content-Type': 'application/json'
                  //           },
                  //           body: data//JSON.stringify({a: 1, b: 'Textual content'})
                  //         });

                  //         const details = await response.json();
                  //         // Three cases to handle:
                  //         //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                  //         //   (2) Other non-recoverable errors -> Show a failure message
                  //         //   (3) Successful transaction -> Show confirmation or thank you message

                  //         // This example reads a v2/checkout/orders capture response, propagated from the server
                  //         // You could use a different API or structure for your 'orderData'
                  //         const errorDetail = Array.isArray(details.details) && details.details[0];

                  //         if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                  //           return actions.restart();
                  //           // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                  //         }

                  //         if (errorDetail) {
                  //           let msg = 'Sorry, your transaction could not be processed.';
                  //           msg += errorDetail.description ? ' ' + errorDetail.description : '';
                  //           msg += details.debug_id ? ' (' + details.debug_id + ')' : '';
                  //           alert(msg);
                  //         }

                  //         // Successful capture! For demo purposes:
                  //         console.log('Capture result', details, JSON.stringify(details, null, 2));
                  //         const transaction = details.purchase_units[0].payments.captures[0];
                  //         alert('Transaction ' + transaction.status + ': ' + transaction.id + 'See console for all available details');
                  //       } catch (error) {
                  //         console.error(error);
                  //         // Handle the error or display an appropriate error message to the user
                  //       }
                  //     }
                  //   }).render("#paypal-button-container");
                  // })               
 

  </script>
</body>

</html>