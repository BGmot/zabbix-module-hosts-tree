# zabbix-module-hosts-tree
Written according to Zabbix official documentation [<https://www.zabbix.com/documentation/current/manual/modules>](https://www.zabbix.com/documentation/current/en/devel/modules/file_structure)

A Zabbix module to show groups/hosts as a tree under Monitoring -> Hosts Tree menu item in Zabbix.
![screenshot](screenshots/zabbix-module-hosts-tree-1.png)

IMPORTANT: pick module version according to Zabbix version:
|Zabbix version | Module version |
|:-------------:|----------------|
|    v1.3.0     |     5.4        |
|    v2.0.0     |   6.0, 6.2     |
|    v3.0.0     |     6.4        |

# How to use
1) Create a folder in your Zabbix server modules folder (by default /usr/share/zabbix/) and copy contents of this repository into that folder.
2) Go to Administration -> General -> Modules click Scan directory and enable the module. You should get new 'Hosts tree' menu item under Monitoring.

## Authors
See [Contributors](https://github.com/BGmot/zabbix-module-hosts-tree/graphs/contributors)
