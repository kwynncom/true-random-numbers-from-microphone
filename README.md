# true-random-numbers-from-microphone
true random numbers from microphone

From a microphone input, I am generating true random numbers that pass the "rngtest" command.  

USAGE

I create a FIFO (currently /tmp/ns_kwynn_com_2020_11_1_hwrand) and write random numers to it, so there are many options.  One is

terminal 1: 
php index.php 

terminal 2:
od -x /tmp/ns_kwynn_com_2020_11_1_hwrand


MORE DETAILS

I am using a desktop (Ubuntu Linux) microphone input with no microphone attached.  On the software / settings side, the microphone input is turned all the way up.  

For each sound sample, I am taking the smallest-order byte out of 4 byte precision.

Higher bytes will usually be 0 or a fixed number because there is no microphone attached and thus no large dynamic (quiet to loud) variation.  I am assuming I am 
picking up electric noise.  When a sample file is played, it sounds like white noise.

My settings are

arecord -f S32_LE -c 2 -d 1 -r 48000 --device="hw:0,0" 

which means: 32 bit (4 byte) signed precision sound samples, little endian (bytes with lower addresses are the smallest order bits), 2 channels (stereo), 
48,000 samples per second.  I think I have to record in stereo, and 32 bits and 48,000 samples are the highest my hardware / software supports.  "-d 1" is one second 
duration.  

I have done limited experimentation with a microphone attached.  I know that if the microphone is gently tapped with my finger, the sound is loud enough to 
spike the data and make it non-random.  An obvious experiment is to try with the software input settings turned to zero / muted, which should either pass or 
fail on a laptop mic that can't be easily turned off physically.

My first versions only used one of the 2 channels.  Further testing shows that using both stereo channels is fine.  

I've done several tests up to 20 seconds.  The failure rate seems comparable to a system using rngd with the rdrand CPU instructions available.  I understand that 
rdrand is known to be intentionally compromised, so "my" technique may still be useful.  "My" technique is almost certainly much slower than rdrand, though, given 
CPU-generated randomness versus a sample rate correlated to human hearing perception. 

44 is the size of a WAV (RIFF) header - http://www.topherlee.com/software/pcm-tut-wavformat.html

I have to throw out between 40,000 - 50,000 bytes because they are not random until that range.  I am not sure why this is.  One theory is that it takes a moment for the
recording system to calibrate how loud the input is.

*************
REQUIREMENTS

sudo apt install alsa-utils
sudo apt install rng-tools
******
OPTIONAL INSTALLS

[/opt/kwynn/]kwutils.php is part of https://github.com/kwynncom/kwynn-php-general-utils

Its main use in this case is that it turn warnings, notices, etc. into exceptions that I don't catch in this case, so the program dies.  This setting will keep you 
from getting pages of annoying errors; in such an event, the first one will kill the program.  
**************
FIFO VERSION TESTING

TEST 1:

terminal 1:
php index.php

terminal 2:
sudo rngd -r /tmp/ns_kwynn_com_2020_11_1_hwrand

terminal 3:

od -x /dev/random
**********************
TEST 2:

As long as index.php is running:

rngtest -c 100 < /tmp/ns_kwynn_com_2020_11_1_hwrand

(see more on rngtest below)


***** 
RNGTEST

sudo apt install rng-tools

rngtest (v5) - Copyright (c) 2004 by Henrique de Moraes Holschuh

With small input--a 1 second recording--sample output:

rngtest 5
Copyright (c) 2004 by Henrique de Moraes Holschuh
This is free software; see the source for copying conditions.  There is NO warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

rngtest: starting FIPS tests...
rngtest: entropy source drained
rngtest: bits received from input: 334000
rngtest: FIPS 140-2 successes: 16
rngtest: FIPS 140-2 failures: 0
rngtest: FIPS 140-2(2001-10-10) Monobit: 0
rngtest: FIPS 140-2(2001-10-10) Poker: 0
rngtest: FIPS 140-2(2001-10-10) Runs: 0
rngtest: FIPS 140-2(2001-10-10) Long run: 0
rngtest: FIPS 140-2(2001-10-10) Continuous run: 0
rngtest: input channel speed: (min=6.209; avg=15.685; max=18.626)Gibits/s
rngtest: FIPS tests speed: (min=51.971; avg=61.243; max=62.949)Mibits/s
rngtest: Program run time: 5048 microseconds


FURTHER TESTING / MOVING TOWARDS PERMANENT USAGE

Now that I've moved to the FIFO version, you'd have to write a WAV file.  

One option is

terminal 1:
aplay ns_kwynn_com_2020_11_1_hwrand

It will wait for input.  Then run:

terminal 2:
php index.php

There may be some disruption because the play gets ahead of the input.  Another option is

Terminal 1:
head -c 200000 ns_kwynn_com_2020_11_1_hwrand > head1.wav

It will wait, then as above, run my program.  Then 

aplay head1.wav


In terminal 1, read from /dev/random:

od -x /dev/random

Without a specialized source, this will quickly slow down / block.

Modify the file_put_contents of this program to write to /tmp/rand.wav or whatever you want.  (I note below why I call it a WAV file.) 
Run this program and save the file.  

Take one more look at what line the /dev/random read is at, then, in terminal 2:

sudo rngd -r /tmp/rd/rand.wav

/dev/random should output quite a bit of data immediately, in proportion to the random file size.

Note that if you feed rngd an Ubuntu ISO or some such, it will silently reject the file as non-random, and /dev/random will not advance based on the ISO 
file.  (It will advance a bit based on your keystrokes and mouse movement.)


WAV FILES

I call it a wav file simply because the following is a test of randomness.  If you get pure static / white noise, that's a good sign of randomness:

aplay rand.wav

aplay will play it as (hopefully) static as-is.  Note that VLC and presumably others need a WAV header.  I address that in "RELATED READING" below.

Note that aplay will wait for a fifo, such as:

terminal 1: 

mkfifo rng
aplay ./rng

It will block / hang / wait.

terminal 2:

cat rand.wav > rng

aplay will play the file.


RELATED READING

In the following blog entry, with audio file, I show a non-music file played as "music" that miserably fails randomness:
https://kwynn.com/t/7/11/blog.html#2020-1120-arb-file-music

https://wiki.archlinux.org/index.php/Rng-tools


FUTURE WORK

I'm reasonably sure the same principle can be used to create a lightning detector.  In the case of the lightning detector, one would use more bytes or 
all bytes.  I believe it would also work without any wire / headphones at all.

Regarding lightning detection:

https://thehackerdiary.wordpress.com/2017/05/24/lightning-detector-with-nothing-but-a-headphone-jack/

Lightning detector with a simple headphone jack  MAY 24, 2017 by 153ARMSTRONG


CODE HISTORY

const version = 'v0.0.4 - first FIFO version - 2020/11/23 00:19AM EST / GMT -5';
2020/11/20, Friday, 4:40pm - project created moments ago, first code 3 minutes later
