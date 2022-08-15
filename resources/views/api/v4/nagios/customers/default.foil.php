#
# This file contains Nagios configuration for customers' VLAN interfaces
#
# WARNING: this file is automatically generated using the
#   api/v4/nagios/customers/{vlan}/{protocol} API call to IXP Manager.
# Any local changes made to this script will be lost.
#
# See: http://docs.ixpmanager.org/features/nagios/
#
# You should not need to edit these files - instead use your own custom skins. If
# you can't effect the changes you need with skinning, consider posting to the mailing
# list to see if it can be achieved / incorporated.
#
# VLAN id: <?= $t->vlan->id ?>; protocol: ipv<?= $t->protocol ?>; tag: <?= $t->vlan->number ?>; name: <?= $t->vlan->name ?>.
#
# Generated: <?= now()->format( 'Y-m-d H:i:s' ) . "\n" ?>
#
#
# The following objects are used by inheritance here and need to be defined by your own configuration:
#
# 1. Hose definition:    <?= $t->host_definition ?>;
# 2. Service definition: <?= $t->service_definition ?>; and
# 3. Ping service definition: <?= $t->ping_service_definition ?>.
#
# You would create these yourself by creating a configuration file containing something like:
#
# define host {
#     name                    ixp-manager-member-host
#     check_command           check-host-alive
#     check_period            24x7
#     max_check_attempts      10
#     notification_interval   120
#     notification_period     24x7
#     notification_options    d,u,r
#     contact_groups          admins
#     register                0
# }
#
#
# define service {
#     name                    ixp-manager-member-service
#     check_period            24x7
#     max_check_attempts      10
#     check_interval          5
#     retry_check_interval    1
#     contact_groups          admins
#     notification_interval   120
#     notification_period     24x7
#     notification_options    w,u,c,r
#     register                0
# }
#
# define service {
#     name                    ixp-manager-member-ping-service
#     use                     ixp-manager-member-service
#     service_description     PING
#     check_command           check_ping!250.0,20%!500.0,60%
#     register                0
# }
#
# define service {
#     name                    ixp-manager-member-ping-busy-service
#     use                     ixp-manager-member-service
#     service_description     PING-Busy
#     check_command           check_ping!1000.0,80%!2000.0,90%
#     register                0
# }


<?php
    // some arrays for later:
    $all       = [];
    $switches  = [];
    $cabinets  = [];
    $locations = [];
?>

<?php foreach( $t->vlis as $vli ): ?>

###############################################################################################
###
### <?= $vli['cname'] . "\n" ?>
###
### <?= $vli['location_name'] ?> / <?= $vli['abrevcname'] ?> / <?=  $vli['sname'] ?>.
###
<?php
    if( !$vli['enabled'] || !$vli['address'] ) {
        echo "\n\n## ipv{$t->protocol} not enabled / no address configured, skipping\n\n";
        continue;
    }

    $hostname = $t->nagiosHostname( $vli['abrevcname'], $vli['autsys'], $t->protocol, $vli['vid'], $vli['vliid'] );

    $all[]                                = $hostname;
    $switches[ $vli['sname'] ][]          = $hostname;
    $cabinets[ $vli['cabname'] ][]        = $hostname;
    $locations[ $vli['location_name'] ][] = $hostname;
?>

### Host: <?= $vli['address'] ?> / <?= $vli['hostname'] ?> / <?= $vli['vname'] ?>.

define host {
    use                     <?= $t->host_definition ?>

    host_name               <?= $hostname ?>

    alias                   <?= $vli['cname'] ?> / <?= $vli['sname'] ?> / <?= $vli['vname'] ?>.
    address                 <?= $vli['address'] ?>

<?php if( !$vli['canping'] ): ?>
    ## 'canping' is set to false for this vlan interface, disabling host check:
    check_command           null
<?php endif; ?>
}

### Service: <?= $vli['address']  ?> / <?= $vli['hostname'] ?> / <?= $vli['vname'] ?>.

<?php if( !$vli['canping'] ): ?>

# canping disabled for this, skipping service ping check

<?php else: ?>

define service {
    use                     <?= $vli['busyhost'] ? $t->ping_busy_service_definition : $t->ping_service_definition ?>

    host_name               <?= $hostname ?>

}

<?php endif; ?>

<?php endforeach; ?>




###############################################################################################
###############################################################################################
###############################################################################################
###############################################################################################
###############################################################################################
###############################################################################################


###############################################################################################
###
### Group: by switch
###
###
###

<?php foreach( $switches as $k => $c ):
    asort( $c ); ?>

define hostgroup {
    hostgroup_name  switch-ipv<?= $t->protocol ?>-vlanid-<?= $t->vlan->id ?>-<?= preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $k ) ) ?>

    alias           All IPv<?= $t->protocol ?> Members Connected to Switch <?= $k ?> for VLAN <?= $t->vlan->name ?>

    members         <?= $t->softwrap( $c, 1, ', ', ', \\', 20 ) ?>

}

<?php endforeach; ?>


###############################################################################################
###
### Group: by rack
###
###
###

<?php foreach( $cabinets as $k => $c ):
    asort( $c ); ?>

define hostgroup {
    hostgroup_name  rack-ipv<?= $t->protocol ?>-vlanid-<?= $t->vlan->id ?>-<?= preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $k ) ) ?>

    alias           All IPv<?= $t->protocol ?> Members in Rack <?= $k ?> for VLAN <?= $t->vlan->name ?>

    members         <?= $t->softwrap( $c, 1, ', ', ', \\', 20 ) ?>

}

<?php endforeach; ?>


###############################################################################################
###
### Group: by facility
###
###
###

<?php foreach( $locations as $k => $l ):
    asort( $l ); ?>

define hostgroup {
    hostgroup_name  facility-ipv<?= $t->protocol ?>-vlanid-<?= $t->vlan->id ?>-<?= preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $k ) ) ?>

    alias           All IPv<?= $t->protocol ?> Members at Facility <?= $k ?> for VLAN <?= $t->vlan->name ?>

    members         <?= $t->softwrap( $l, 1, ', ', ', \\', 20 ) ?>

}

<?php endforeach; ?>


###############################################################################################
###
### Group: all
###
###
###

<?php asort( $all ); ?>

define hostgroup {
    hostgroup_name  all-ipv<?= $t->protocol ?>-vlanid-<?= $t->vlan->id ?>

    alias           All IPv<?= $t->protocol ?> Members for VLAN <?= $t->vlan->name ?>

    members         <?= $t->softwrap( $all, 1, ', ', ', \\', 20 ) ?>

}

### END ###