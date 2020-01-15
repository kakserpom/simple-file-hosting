{extends template="layout.tpl"}
    {block title}Hello world!{/block}
    {block Meta_Description}Description{/block}
    {block Meta_Keywords}Keywords {/block}
    {block content}

 <h1 class="mt-5"></h1>
        <form class="input-message" method="post">

               <input class="dropify" data-name="file" type="file">
          </form>


{/block}

{/extends}
