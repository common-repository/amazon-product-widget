<div class="wrap [[IDENTIFIER]]-options" id="[[IDENTIFIER]]">
    <h2>[[NAME]]</h2>
</div>

[[NOTICE]]
[[ERROR]]

<p>Gladly welcome to the <em>[[NAME]]</em> configuration page!</p>
<p>[[HELP]]</p>

<form action="[[FORM-ACTION]]" method="post">
    <!-- option update/save trigger -->
    <input type="hidden" name="action" value="update" />

    <div id="apw-main">
        <h3>Main Options</h3>
        <table class="form-table">
            <tbody>
                [[FORM-FIELDS]]
            </tbody>
        </table>
    </div>
    <br />
    <p class="submit">
        [[FORM-SUBMIT]]
    </p>
</form>


<script language="javascript" type="text/javascript">
function submitClearCache(f)
{
    if (document.getElementById('apw_clearcache').checked) {
        if (confirm('Are you sure want to clear the cached data?')) {
            f.submit();
        }
    } else {
        alert('Please check the box to confirm first!');
    }
}
</script>

<form action="[[FORM-ACTION]]" method="post" onsubmit="submitClearCache(this); return false;">
    <!-- option update/save trigger -->
    <input type="hidden" name="action" value="clearcache" />

    <div id="apw-cache">
        <h3>Cache</h3>
        <p>The data for each ASIN get cached after the first request. You can clear this cached data by checking the box below and submitting this form.</p>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="apw_clearcache">Clear cached data:</label>
                    </th>
                    <td>
                        <input type="checkbox" name="apw_clearcache" id="apw_clearcache">
                        <span class="help">Check this box to clear the cached data.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <p class="submit">
        [[FORM-SUBMIT]]
    </p>
</form>
