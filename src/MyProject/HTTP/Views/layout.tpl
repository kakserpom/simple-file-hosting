{?$layoutName = 'main'}
{?$bundles = ['main']}

{if !$isPjax}<!DOCTYPE html>
<!doctype html>
<html class="no-js" lang="">
{/if}

<head prefix="og: http://ogp.me/ns#">
    <title>{block title}{/block}</title>
    <meta name="csrf-token" content="{$csrfToken}">
    <!-- Bootstrap core CSS -->
    <script type="text/javascript" src="https://cdn.rawgit.com/dmauro/Keypress/master/keypress.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" rel="stylesheet">
    <link href="/dist/node_modules/jtable/lib/themes/metro/lightgray/jtable.min.css" rel="stylesheet" type="text/css" />


    <link href="/dist/node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dist/node_modules/dropify/dist/css/dropify.min.css" rel="stylesheet">

     {if $env === 'prod'}
        {foreach from=$bundles item=$bundle}
            <link rel="stylesheet" type="text/css" href="/dist/{$bundle}.bundle.min.css?{$buildTimestamp}">
        {/foreach}
    {else}
        {foreach from=$bundles item=$bundle}
            <link rel="stylesheet" type="text/css" href="/dist/{$bundle}.bundle.css?{time()}">
        {/foreach}
    {/if}
    <meta http-equiv="x-pjax-version" content="{$this->pjaxVersion($layoutName)}">
    <meta charset="utf-8">

    <script type="text/javascript">
    var USER = {if $user}{($user->toPublicArray())|json_encode|html}{else}null{/if};
    </script>
</head>

  <body id="pjax-container">
    <header>
      <!-- Fixed navbar -->
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="{__ url('root')}">Simple File Hosting</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
        </div>
      </nav>
    </header>

    <!-- Begin page content -->
    <main role="main" class="container">
        {block content}{/block}
    </main>

    <footer class="footer">
      <div class="container">
        <span class="text-muted">Developed by @kakserpom</span>
      </div>
    </footer>

{if $tracy}{?$tracy->renderBar()}{/if}

{if !$isPjax}
<!-- footer -->
{/if}
<!-- Generated in {round(microtime(true) - $quicky.server.REQUEST_TIME_FLOAT, 5)} sec. -->
</body>
{if !$isPjax}

{if $env === 'prod'}
{foreach from=$bundles item=$bundle}
    <script type="text/javascript" src="/dist/{$bundle}.bundle.min.js?{$buildTimestamp}"></script>
{/foreach}
{else}
{foreach from=$bundles item=$bundle}
    <script type="text/javascript" src="/dist/{$bundle}.bundle.js?{time()}"></script>
{/foreach}
{/if}

    </html>
{/if}
