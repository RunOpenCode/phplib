CREATE DATABASE IF NOT EXISTS `component_query_foo`;
CREATE DATABASE IF NOT EXISTS `component_query_bar`;
CREATE DATABASE IF NOT EXISTS `component_query_baz`;

GRANT ALL ON `component_query_foo`.* TO 'roc'@'%';
GRANT ALL ON `component_query_bar`.* TO 'roc'@'%';
GRANT ALL ON `component_query_baz`.* TO 'roc'@'%';
