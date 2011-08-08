#! /bin/bash

#
#===================================================================
#  tf2stats-webui
#-------------------------------------------------------------------
# Copyright:    (c) 2009 - 2011 -- Damian Bushong
# License:      MIT License
#
#===================================================================
#
# This source file is subject to the MIT license that is bundled
# with this package in the file LICENSE.
#

##########################################
# begin script config
##########################################

# files to exclude in phar-build
EXCLUDE="~$ .*\.txt$ .*\.xml$ .*\.markdown$ .*\.md$ stub\.php .*\.json$"
# directories to exclude in phar-build
EXCLUDEDIR="/\.git/ /\.svn/"
# source directory
SRC="./package/"
# name of the phar archive
PHARNAME=tf2stats.phar

##########################################
# end script config
##########################################

# get this script's full path
SCRIPT=`dirname $(readlink -f $0)`
#cd $SCRIPT/../

echo "compiling phar for tf2stats web interface"
phar-build --phar $SCRIPT/$PHARNAME -s $SCRIPT/../$SRC -x "$EXCLUDE" -X "$EXCLUDEDIR" -p $SCRIPT/../keys/priv.pem -P $SCRIPT/../keys/pub.pem
mv $SCRIPT/$PHARNAME $SCRIPT/../lib/$PHARNAME
mv $SCRIPT/$PHARNAME.pubkey $SCRIPT/../lib/$PHARNAME.pubkey
echo 'phar compilation successful'
