{include file='admin/head.tpl' title='User'}
<body>

    {include file='admin/nav.tpl'}

    <div class="container">

        <h1>{$sitename} User</h1>

        <form action="{$path}/user/" method="post">
            <label for="username">Name</label>
            <input type="text" class="span3" id="username" name="username" placeholder="Name" value="{if isset($data.username)}{$data.username}{/if}" />
            <span class="help-inline">&nbsp;</span>

            <label for="email">Email</label>
            <input type="text" class="span3" id="email" name="email" placeholder="Email" value="{if isset($data.email)}{$data.email}{/if}" />
            <span class="help-inline">&nbsp;</span>

            <label for="password">New Password (blank to keep current)</label>
            <input type="password" class="span3" id="password" name="password" placeholder="Password" />
            <span class="help-inline">&nbsp;</span>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>

    </div>

</body>
</html>