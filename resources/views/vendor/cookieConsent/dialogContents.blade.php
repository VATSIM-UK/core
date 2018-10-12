<div class="js-cookie-consent cookie-consent">

    <div id="message">
        <span class="cookie-consent__message">
            {!! trans('cookieConsent::texts.message') !!}
        </span>
    </div>

    <div id="button">
        <button class="js-cookie-consent-agree cookie-consent__agree btn btn-md btn-primary px-7">
            {{ trans('cookieConsent::texts.agree') }}
        </button>
    </div>
</div>

<style>
    .cookie-consent {
        position: fixed;
        bottom: 0;
        background-color: #17375E;
        display: flex;
        justify-content: space-between;
        width: 100%;
        border-top: 5px solid #00b0f0;
        z-index: 999;
    }

    #message {
        padding: 0.5rem;
        color: #F1F1F1;
        font-family: "Open Sans", "sans-serif";
        font-size: 16px;
    }

    #button {
        padding: 0.5rem;
    }
</style>
