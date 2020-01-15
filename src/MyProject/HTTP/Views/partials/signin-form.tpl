<div id="signin-popover" style="display:none">
    <form class="signin-form" action="{__ url('user', 'signin')}" method="post">
        <div class="form-group">
            <input type="text" name="email" class="form-control" placeholder="E-Mail" required="required">
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Пароль" required="required">
        </div>
        <div class="form-group">
            <button type="submit" data-loading-text="<i class='fa fa-spinner fa-spin'></i>" class="btn btn-primary btn-block btn-lg">Войти</button>
        </div>
        <div class="clearfix">
            {*<label class="pull-left checkbox-inline"><input type="checkbox"> Запомнить меня</label>*}
            <a href="{__ url('user','reset-password')}" class="pull-right">Забыли пароль?</a>
        </div>
    </form>
</div>