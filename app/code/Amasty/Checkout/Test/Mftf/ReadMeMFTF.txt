ReadeMeMFTF (recommendations for running tests related to One Step Checkout extension).

     38 One Step Checkout specific tests, grouped by purpose, for greater convenience.

            Tests group: OSC
            Runs all tests except OSCCheckExternalPayments (online payment methods) group.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSC -r

            Tests group: OSCConfiguration
            Runs tests related to extension configuration.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCConfiguration -r

            Tests group: OSCFunctional
            Runs tests related to extension's core functions.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCFunctional -r

            Tests group: OSCPaymentMethods
            Runs tests related to offline payment methods' work with One Step Checkout.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCPaymentMethods -r
            Included payment method tests:
            Bank Transfer, Cash On Delivery, Purchase Order
                SSH command to run tests for particular payment method:
                vendor/bin/mftf run:group OSCPaymentPurchaseOrder -r
                vendor/bin/mftf run:group OSCPaymentCashOnDelivery -r
                vendor/bin/mftf run:group OSCPaymentBankTransfer -r

            ---

            Here and below:
            to run groups of tests related to online payment methods, it is necessary to add test credentials for needed methods at (for Composer based installs)
            vendor/amasty/module-common-tests/Test/Mftf/Data/PaymentMethodsCredentialsData
            or (for install-by-upload)
            app/code/Amasty/CommonTests/Test/Mftf/Data/PaymentMethodsCredentialsData
            then to add test card details at (for Composer based installs)
            vendor/amasty/module-common-tests/Test/Mftf/Data/CreditCardsData
            or (for install-by-upload)
            app/code/Amasty/CommonTests/Test/Mftf/Data/CreditCardsData

            (!) Please note that due to test framework limitations it is currently impossible to revert payment methods configuration to pre-test-run condition.
            To avoid cases where live Magento instances may end up with test/sandbox payment methods mode after running tests, online payment method configuration parts (such as API Username/Password, Merchant ID, etc) will be emptied after its tests run.
            Please make sure that all relevant details are at hand prior to running this group of tests (and/or particular tests from it) at live instances.

            Tests group: OSCCheckExternalPayments
            Runs tests related to external payment methods' work with One Step Checkout.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCPaymentMethods -r
            Included payment method tests:
            Amazon, Authorise, Braintree, EWay, Klarna, PayPal, PayflowPro, Stripe
                SSH command to run tests for particular payment method:
                vendor/bin/mftf run:group OSCCheckExternalPaymentsAmazon -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsAuthorise -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsBraintree -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsEWay -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsKlarna -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsPayPal -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsPayflowPro -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsStripe -r