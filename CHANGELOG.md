# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/) as of version 1.0.0.

## [1.0.0] - 2016-10-04

### Added
- Add a help text for the filter module
- Add option to (not) display default type column
- Add first draft (incomplete) to provide french
- SPARQL: add option to export result to CSV
- Add hide type when using add instance
- Add json-support for registrationAction
- Add prependTitleProperty again, remove unused methods and keep a single instance of erfurt
- Add showHiddenElements to naviagtion config
- Add hugarian to language selection
- Add initial ResourceJsonrpcAdapter
- Add getTitle method to ModelJsonrpcAdapter
- Add RDF/JSON (Alternate, Talis) content type
- Add support for attributes in toolbar buttons
- Add support for docker based devenv
- Add controller test for resource export action
- Add an onRegisterUser Event
- Add an onRegisterUserFailed Event
- Add test makefile for new composer download

### Changed
- Update RDFauthor
- Use onResolveDomainAliasInput on URL detection
- TitleHelper improvements: Chunk/Bulk Querying
- Changing the workflow of the TitleHelper using the ResourcePool
- Update getValues functionality using the ResourcePool - Scalability Improvement
- Changing getAllProperties functionality to work with the MemoryModel and ResourcePool
- Changing debug workflow with blanknodes on gettitles + cleanup
- Changing debug workflow on addResource
- Re-enabling inverse show properties in table view but on a scaleable way
- Change sameTerm Filter into Equals constraint in NavigationController
- Make new TitleHelper aware of languages
- Add getResources() call and unify testShownPropertiesAddTitles
- Set zend version to latest 1.x. Fix #306.
- Move responsibility for contentType and fileExtension to Erfurt
- Improve Exception output
- Update jQuery to 1.9.1 and add jQuery migrate
- Using composer for dependencies
- Update jquery-ui to 1.8.22. Fix #337
- Refactor usage of exit to using return
- Clean up default.ini/config.ini.dist, disable caches per default
- Remove trim() to give more meaningfull output
- Improve FileCommentSniff to also accept older years and to ignore sub repos
- Increse minimal PHP version to 5.4.0
- Change sort option to SORT_NUMERIC
- Modify help menu building process
- Deactivate ckan, mail and pingback extension
- Add data route to default config
- Update Linked Data plugin to use data route
- Enhance environment for controller unit tests
- Replace sameterm with equals in navbox
- Use IN where possible
- Optimize offset/limit usage & refactor js code a bit
- Make codesniffer now ignores the FileCommentSniff and make codesniffer_year includes the Sniff
- Disable front-end cache in config.ini.dist
- Change the Documentation link from Github to the new Documentation

### Fixed
- Fix type handling in rdfauthor popover controller
- Fix #278 Add Property not working properly
- Complete license and copyright information in doc blocks
- Additional fixes while firing onRegisterUser and onRegisterUserFailed event
- added workaround to handle statements with empty object URIs, which are sometimes provided by RDFauthor editor (seems to be related to line 467 in extensions/themes/silverblue/scripts/support.js)
- Only Uris should be used as links in a List. BugFix
- Prevent Bugs with wrong initalized class attributes
- Fix try to get property of non-object, when deleting model
- Fix pingback takes for ever
- Fix #286. Also consider colon in OntoWiki_Utils::compactUri method.
- Fix #258. Forward Erfurt.
- Fix community tab
- Fix selectlanguage config for russian
- Fix comments if creator not set.
- Fix getting branch name (wasn't language independent)
- Fix #145. Fix special chars in Linked Data request URIs.
- Fix testing for extensions
- Fix handling of protocols forwarded by proxy. Fix #313.
- Fix typo in Bootstrap comment
- Fix 319. Expose navigationAddElement in navigation.js
- Fix setting select clause instead of prologue part and forward Erfurt
- Fall back if no feed is specified
- Fix #347. Remove require_once because we are using autoloader now
- Fix skipped test
- Fix integration tests to fit new result format
- #332: Fix "Shift + Alt" key capture issue
- Fix content-type for resource exports
- Fix support for JSON content types in Linked Data plugin
- Fix indentation of mappings array

### Removed
- Removing subjects from inverse relations memory model
- Remove OntoWiki_Controller_Service. Fix #242.
- Remove useless add-method from ResourceJsonrpcAdapter
- Remove Vagrant development environment
- Remove test target in makefile

## [0.9.11] - 2014-01-31

- Improve model selection in linkeddataserver extension
- Extend cache clear script by support for query and translation cache
- Fix #60: Add script to clear cache via shell
- fix #201 - removed css classes for modal and set z-index of applicationbar to 999
- Fix 271. Fix Output Format of queries editor
- fix #201 - fixed z-index for modal-wrapper-propertyselector
- Merge pull request #268 from hknochi/feature/fix-extension-enabler
- Merge pull request #269 from hknochi/feature/fix-pagination
- fix issue #167 - invalidate extensionCache to distribute requested changes
- fix issue #261 - added limit-parameter to url
- improve cron job
- Add list-events target to Makefile
- remove log in about screen
- avoid broken feed URLs on version / names with spaces
- use configure name instead of hardcoded one
- fixed issue #201 - added css rules for modal-wrapper
- fixed issue #201 - added css rules for simplemodal-container and -overlay
- Fix build to ignore worker shell scripts
- Reorganize shell scripts to avoid build failure
- Cleanup mail extension job class by removing obsolete members
- Added console client and an example extension for testing Erfurts worker
- Add support for Erfurts background jobs
- add min-height to <span> in resource view
- Add hidden save and cancle buttons in listmode
- Reorganize some code in support.js
- Fix editProperty to work in extended mode (+)
- Allow configuration of session cookie parameters, such as session lifetime
- Fix #259. Write alle defined prefixes to RDFa output.
- Fix warning, when site redirect takes place. Fix #260.
- fix wrong server class includes (closes #256)
- initial version of SelectorModule
- Fix model creation if no title is specified
- move inner window modules outside of master form
- Add “View as Resource” entry to model menu. Fix #152
- fix wrong encoded query parameter
- add even/odd classes for row and noodds parameter
- use new querylink feature of table partial
- remake table partial, add query link enhancement
- Fix #152. Move menu creation to Menu_Registry.
- add xdebug link
- Remove unused action service/entities

## [0.9.10] - 2013-07-11

- new model creation / add data procedure
- fixes in query editor
- performance issues in title helper
- unify CommentModule and LastcommentsModule, increase limit and set ordering
- +100 other commits
- depends on Erfurt 1.6
- depends on RDFauthor 0.9.6

## [0.9.8] - 2013-02-01

- fix cors header
- add doap:wiki to the weblink list (2 weeks ago by Sebastian Tramp)
- add head action to return the request header for given uri
- fix extensions setting page (toggleswitch) #179
- Fixing Sort functionality of the Navigation Box extension re-enabling the Sort Menue
- prevent open paranthesis bug while using the limit buttons (100 / all)
- Fix #172 - rewrite rules in .htaccess
- use getReadableGraphsUsingResource method on Store
- cleanup togglebutton changes
- fix toggle button for new jquery version (#167)
- depends on Erfurt 1.5
- depends on RDFauthor 0.9.5

## [0.9.7] - 2012-11-27

- GUI niceups
- lots of fixes
- https://github.com/AKSW/OntoWiki/issues?milestone=1&page=1&state=closed
- RDFauthor is now a separate package
- Add support for additonal vhosts
- increment RDFauthor dependency
- allow usage of virtuosos bd.ini if present
- add gnome desktop file

## 0.9.6-21 - 2012-03-03

- fix RDFauthor integration
- forward RDFauthor to b780680
- forward OntoWiki to 04b33fd

[1.0.0]: https://github.com/AKSW/OntoWiki/compare/v0.9.11...v1.0.0
[0.9.11]: https://github.com/AKSW/OntoWiki/compare/v0.9.10...v0.9.11
[0.9.10]: https://github.com/AKSW/OntoWiki/compare/v0.9.8...v0.9.10
[0.9.8]: https://github.com/AKSW/OntoWiki/compare/v0.9.7...v0.9.8
[0.9.7]: https://github.com/AKSW/OntoWiki/compare/v0.9.6-21...v0.9.7
