# Exploratory testing of the article contract projection chain

This guide covers the first end-to-end testable vertical slice. The same `Article` contract produces Joomla REST
routes, an OpenAPI document and five MCP tools. Generated MCP tools dispatch to the existing Joomla API controller
inside the current process; no loopback HTTP request is made.

## Backwards compatibility

The internal MCP dispatcher is additive and does not replace or modify Joomla's established REST execution path.

- External REST requests still use `ApiApplication`, the API router and the existing component dispatcher.
- Existing API controllers, webservices plugins and third-party routes require no changes.
- Existing MCP tools continue to implement the unchanged `ToolInterface` contract.
- `OperationInvokerInterface` remains unchanged.
- `HttpOperationInvoker` remains available as a fallback implementation, but generated article tools use the internal
  dispatcher by default.

The internal dispatcher boots the target component through Joomla's extension manager and uses the component's own
dispatcher and MVC factory. Existing controllers therefore receive an isolated API application and input object while
continuing to use their established controller tasks. A temporary Factory bridge supports legacy code which still
calls `Factory::getApplication()` or `Factory::getDocument()`.

## Prerequisites

- A non-production Joomla installation built from the `contract-projection-chain` branch.
- The **Web Services - Content** and **Web Services - MCP** plugins enabled.
- The **MCP - Joomla** plugin enabled.
- A Joomla API token belonging to a user with the required content permissions.
- A valid TLS certificate when Claude Code connects over HTTPS.

The internal dispatcher does not require the PHP cURL extension and does not require the site to call its own public
URL. It is therefore also suitable for a single-process development server, subject to the normal limitations of that
server.

After updating the branch, remove Joomla's generated namespace cache if it exists:

```bash
rm -f administrator/cache/autoload_psr4.php
```

Run the contract smoke test from the project root:

```bash
php tests/run-smoke.php
```

The command should report `Article operation chain smoke test passed.` It also writes:

- `article-openapi.json`, which can be imported into Postman;
- `article-mcp-tools.json`, which shows the five generated MCP tool definitions.

Run the focused PHPUnit tests when the complete Joomla test environment is available:

```bash
php vendor/bin/phpunit \
  tests/Unit/Libraries/Cms/WebService/Internal \
  tests/Unit/Components/ComMcp/Api/Tool/InternalApiOperationInvokerTest.php
```

## REST testing with Postman

External REST behaviour is unchanged. Use the standard Joomla API header:

```text
X-Joomla-Token: YOUR_JOOMLA_API_TOKEN
Accept: application/vnd.api+json
```

### List articles

```http
GET https://example.test/api/index.php/v1/content/articles
```

Optional query examples:

```text
filter[category]=2
filter[search]=contract
list[ordering]=created
list[direction]=desc
```

### Read one article

```http
GET https://example.test/api/index.php/v1/content/articles/42
```

### Create a test article

Use a category that exists in the test installation:

```http
POST https://example.test/api/index.php/v1/content/articles
Content-Type: application/json
```

```json
{
  "title": "Contract projection test",
  "articletext": "Created through the generated REST contract.",
  "catid": 2,
  "language": "*",
  "state": 0
}
```

The canonical resource property is named `category`. The REST projection exposes the established Joomla transport
name `catid`; the MCP tool accepts `category` and maps it to `catid` before dispatching the controller task.

### Update a test article

```http
PATCH https://example.test/api/index.php/v1/content/articles/42
Content-Type: application/json
```

```json
{
  "title": "Updated contract projection test"
}
```

### Delete a test article

```http
DELETE https://example.test/api/index.php/v1/content/articles/42
```

Only use create, update and delete against disposable content in a non-production installation.

## MCP testing with Claude Code

Register the remote MCP endpoint:

```bash
claude mcp add --transport http joomla-contracts \
  https://example.test/api/index.php/v1/mcp \
  --header "Authorization: Bearer YOUR_JOOMLA_API_TOKEN"
```

Check the connection:

```bash
claude mcp list
claude mcp get joomla-contracts
```

Inside Claude Code, open `/mcp`. The following generated tools should be visible:

```text
content.articles.list
content.articles.get
content.articles.create
content.articles.update
content.articles.delete
```

Suggested exploratory prompts:

```text
Use content.articles.list to list the five most recently created Joomla articles.
```

```text
Use content.articles.get to read article 42.
```

```text
Create an unpublished Joomla test article titled "MCP contract test" in category 2 with the article text
"Created by Claude Code through the generic web service tool".
```

```text
Update article 42 so that its title is "Updated through MCP". Do not change any other property.
```

Claude Code should request confirmation before potentially destructive actions according to its own client behaviour.
The server-side Joomla permissions remain decisive.

## Runtime path

A generated MCP call follows this path:

```text
Claude Code
  -> POST /api/index.php/v1/mcp
  -> WebserviceTool
  -> InternalApiOperationInvoker
  -> ComponentApiDispatcher
  -> existing component dispatcher and MVC factory
  -> existing API controller and task
  -> isolated JSON:API response
  -> flattened structured MCP result
```

The target component is booted normally. The internal request reuses the authenticated identity, configuration,
container, event dispatcher, session and language from the outer API application. Its input, JSON body, document,
headers, status and response output remain isolated from the outer MCP request.

## Transitional limitations

- Only the article CRUD operations are registered through the generic provider in this vertical slice.
- Dynamic custom fields are permitted by the resource contract, but their installation-specific schemas are not yet
  expanded by a property provider.
- The generic REST handler does not yet hydrate the resource DTO itself. External REST continues to use Joomla's
  established API controller while routes and documentation come from the compiled contract.
- Legacy code using `Factory::getApplication()` and `Factory::getDocument()` is covered by a temporary bridge during
  the internal controller task.
- Third-party code which fetches the top-level `ApiApplication` directly from the dependency injection container during
  a controller task may still observe the outer MCP application. Such code bypasses the application injected into the
  component dispatcher and should be identified before the compatibility bridge is removed or tightened.

## Troubleshooting

### The MCP connection works, but article tools are missing

Confirm that **MCP - Joomla** is enabled, remove `administrator/cache/autoload_psr4.php`, and restart the PHP worker or
container so the new classes are discoverable.

### REST succeeds but MCP receives an authorisation error

The internal dispatch uses the identity authenticated for the MCP request. Verify that the token owner has
`core.create`, `core.edit` or `core.delete` for the requested action and asset.

### A third-party controller reads the outer MCP request

Confirm that it uses the application and input injected into its dispatcher or controller. Calls to
`Factory::getApplication()` and `Factory::getDocument()` are bridged during dispatch. Direct container lookup of the
top-level API application is a known transitional limitation.
