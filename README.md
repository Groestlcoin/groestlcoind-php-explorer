This is a blockchain browser which wraps json output in php and html tables.  

this is adequate to use as a blockexplorer to see relevant transactions on
groestlcoin

prerequisites:

 * star the project
 * fork the project

ubuntu in anger:  
 `sudo apt-get install php-cli jq coreutils curl couchdb`

in bash:

`bash -li`

customize and run the generator (named armchair.sh)

```bash
GROESTLCOIND_="~/groestlcoind"                 \
BLOCKCHAINNAME_="groestlcoin"              \
COUCHPREFIX_="http://127.0.0.1:5984"   \
SYM_="grs"                             \
./armchair.sh
```

test:

```bash
pushd target
php -S 0:8080
```

You should expect about equal amount of time importing blocks from your blockchain into couchdb, following ( a long
time after) the first attempts at viewing and the initial view creations for 'top 20 users' and 'total issued'


once this initial view generation is out of the way the explorer should be able to withstand a decent amount of test
traffic on amazon t2 nano node.

'update.sh' will run on a naive incoming request (no url query params) and updates to the views are on demand, and
reasonably responsive.  couchdb 2.x shards view generation so use that if it's important and you have cores to spare.
