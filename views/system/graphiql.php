<script src="//cdn.jsdelivr.net/es6-promise/4.0.5/es6-promise.auto.min.js"></script>
<script src="//cdn.jsdelivr.net/fetch/0.9.0/fetch.min.js"></script>
<script src="//cdn.jsdelivr.net/react/15.4.2/react.min.js"></script>
<script src="//cdn.jsdelivr.net/react/15.4.2/react-dom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/graphiql/3.1.1/graphiql.min.js"></script>
<h3><?php echo _("Explore GraphQL based on scopes")?></h3>
<h4><?php echo _("Paste Scope(s) Here (You can generate scopes in the Scope Visualizer Tab)")?></h4>
<textarea id="scope-explorer" class="form-control"></textarea>
<button id="reload-explorer" class="btn btn-primary"><?php echo _("Reload Explorer (If scope was changed)")?></button>
<div id="graphiql-container"></div>
