const sqgcSelector = '#squareGiftCardForm';  // this needs to be an ID
const sqinfo = document.querySelector(sqgcSelector);
const appId = sqinfo.dataset.applicationId;
const locationId = sqinfo.dataset.locationId;


async function initializeGiftCard(payments) {
    const giftCard = await payments.giftCard();
    await giftCard.attach(sqgcSelector);
    return giftCard;
}

async function tokenize(paymentMethod) {
    const tokenResult = await paymentMethod.tokenize();
    if (tokenResult.status === 'OK') {
      document.querySelector('input[name="square_gc_nonce"]').value = tokenResult.token;
      token = tokenResult.token;
    } else {
      let errorMessage = `Tokenization failed with status: ${tokenResult.status}`;
      if (tokenResult.errors) {
        errorMessage += ` and errors: ${JSON.stringify(
          tokenResult.errors
        )}`;
      }

      throw new Error(errorMessage);
    }

    return token;
}

 function createPayment(tokens) {

  // server payment is just to get the card value - this one gets authorized and then immediately cancelled
  $.request(sqinfo.dataset.gcHandler, {
      data: {
          nonce: token,
      }
  });

  $(window).on('ajaxUpdateComplete', function(){
    // manually trigger update on payment forms (gift card might remove payment options if it covers the full order value)
    $('[name=payment]').trigger('change');
  });

}

document.addEventListener('DOMContentLoaded', async function () {

  let payments;
  try {
      payments = window.Square.payments(appId, locationId);
  } catch {
      const statusContainer = document.getElementById(
      'payment-status-container'
      );
      statusContainer.className = 'missing-credentials';
      statusContainer.style.visibility = 'visible';
      return;
  }

  let giftCard;
  try {
    giftCard = await initializeGiftCard(payments);
  } catch (e) {
    console.error('Initializing Gift Card failed', e);
    return;
  }

  // code for handling tokenization and payments
  const giftCardButton = document.getElementById('squareGiftCardSubmitButton');
  giftCardButton.addEventListener('click', async function (event) {
    await handleGCPaymentMethodSubmission(event, giftCard);
  });

  async function handleGCPaymentMethodSubmission(event, paymentMethod) {
      event.preventDefault();
      
      try {
          const token = await tokenize(paymentMethod);
          const paymentResults = await createPayment(token);
          // tokenize again (requests a new nonce) so that we can make a real GC payment request on checkout submit
          const token_2 = await tokenize(paymentMethod);

      } catch (e) {
          console.error(e.message);
      }
  }

});