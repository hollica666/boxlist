{OVERALL_HEADER}<!-- main -->
<section id="main" class="clearfix page">
    <div class="container">
        <div class="breadcrumb-section"><!-- breadcrumb -->
            <ol class="breadcrumb">
                <li><a href="{LINK_INDEX}"><i class="fa fa-home"></i> {LANG_HOME}</a></li>
                <li>{LANG_FEEDBACK}</li>
                <div class="pull-right back-result"><a href="{LINK_LISTING}"><i class="fa fa-angle-double-left"></i>
                    {LANG_BACK-RESULT}</a></div>
                <h2 class="title">{LANG_FEEDBACK}</h2>
            </ol>
            <!-- breadcrumb --></div>
        <div class="section">
            <div class="feed-back">
                <h3>{LANG_WHAT-YOU-THINK}</h3>

                <p>&nbsp;</p>

                <div class="feed-back-form">
                    <form method="post">
                        <span>{LANG_USER-DETAILS}</span>
                        <input type="text" class="form-control" name="name" placeholder="{LANG_FULL-NAME}" required="">
                        <input type="text" class="form-control" name="email" placeholder="{LANG_EMAIL}" required="">
                        <input type="text" class="form-control" name="phone" placeholder="{LANG_PHONE-NO}">
                        <input type="text" class="form-control" name="subject" placeholder="{LANG_SUBJECT}" required="">
                        <!---728x90---><span>{LANG_ANYTHING-TO-TELL}?</span>
                        <textarea type="text" class="form-control" name="message" placeholder="{LANG_MESSAGE}..."
                                  required=""></textarea>

                        <div class="form-group">
                            IF("{RECAPTCHA_MODE}"=="1"){
                            <div class="g-recaptcha" data-sitekey="{RECAPTCHA_PUBLIC_KEY}"></div>
                            {:IF}
                            <span style="color:red;font-size: 14px">IF("{RECAPTCH_ERROR}"!=""){ {RECAPTCH_ERROR} {:IF}</span>
                        </div>

                        <input type="submit" name="Submit" class="btn btn-outline" value="{LANG_SUBMIT}">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- container --></section>
<!-- main -->
<script src='https://www.google.com/recaptcha/api.js'></script>
{OVERALL_FOOTER}