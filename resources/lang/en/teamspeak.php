<?php


return [
    'registration.notfound.poke' => 'No registration found. Visit https://www.vatsim.uk/dashboard to register.',
    'registration.notfound.kick' => 'No registration found.',
    'registration.notfound.exception' => 'No registration found for :dbid',
    'ban.network.ban' => 'You are currently serving a VATSIM.net ban.',
    'ban.system.ban' => 'You are currently serving a VATSIM UK ban.',
    'inactive' => 'VATSIM account inactive - visit https://cert.vatsim.net/vatsimnet/statcheck.html',
    'notification.mandatory.notify' => 'You must accept the new notifications that are published at https://www.vatsim.uk/dashboard',
    'notification.mandatory.poke' => 'You cannot connect to TeamSpeak until you read the notifications at https://www.vatsim.uk/dashboard',
    'notification.mandatory.kick' => 'You must accept the latest important notifications.',
    'notification.important.poke.1' => 'You have new important notifications to accept at https://www.vatsim.uk/dashboard',
    'notification.important.poke.2' => 'These notifications are highly relevent. Please read them.',
    'nickname.invalid.poke1' => 'Please use your full VATSIM-registered name - you may check this at https://www.vatsim.uk/dashboard/',
    'nickname.invalid.poke2' => 'If you believe this is a mistake, please contact Web Services via https://helpdesk.vatsim.uk/',
    'nickname.invalid.kick' => 'Please use your full VATSIM-registered name.',
    'nickname.partiallyinvalid.poke1' => 'Your current nickname is not allowed. Ensure that your name is your VATSIM-registered one.',
    'nickname.partiallyinvalid.poke2' => 'If you are controlling on the network, please view your TeamSpeak messages for the correct format.',
    'nickname.partiallyinvalid.note' => 'If using a callsign in your name, please ensure that the callsign is as shown on your ATC client, and that your nickname matches the following format: :example. If you have recently connected, please disregard this message, otherwise you must change your nickname as soon as possible.',
    'idle.message' => 'You have been idle in TeamSpeak for at least :idleTime minutes. We encourage members to disconnect if they plan to be away for extended periods. You will be disconnected if you are idle for greater than :maxIdleTime minutes.',
    'idle.poke' => 'You have been idle for more than :idleTime minutes. If you continue to be idle, you will be removed.',
    'idle.kick.poke.1' => 'You have been removed from the TeamSpeak server for remaining idle for more than :maxIdleTime minutes.',
    'idle.kick.poke.2' => 'You are free to reconnect, however please do not remain idle for extended periods.',
    'idle.kick.reason' => 'Idle timeout exceeded.',
];
