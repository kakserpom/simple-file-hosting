{extends template="layout.tpl"}
  {block title}404{/block}
  {block content}
 <div class="row">
        <div class="col-md-12">
            <div class="error-template">
                <h1>
                    Упс!</h1>
                <h2>
                    404 Страница не найдена</h2>
                <div class="error-details">
                    Извините, произошла ошибка. Запрошенная страница не найдена!
                </div>
                <div class="error-actions">
                    <a href="{__ url('root')}" class="btn btn-primary btn-lg"><span class="fa fa-home"></span> На главную </a>
                </div>
            </div>
        </div>
    </div>
  {/block}
{/extends}
