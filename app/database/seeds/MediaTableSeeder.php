<?php

use Illuminate\Database\Seeder;
use App\Gestures;

class GestureTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Static files( Redo)

        $image_urls =   array(
            0 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/1.jpg'),
            1 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/10.jpg'),
            2 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/11.jpg'),
            3 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/12.jpg'),
            4 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/13.jpg'),
            5 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/14.jpg'),
            6 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/15.jpg'),
            7 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/16.jpg'),
            8 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/17.jpg'),
            9 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/18.jpg'),
            10 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/19.jpg'),
            11 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/2.jpg'),
            12 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/20.jpg'),
            13 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/21.jpg'),
            14 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/22.jpg'),
            15 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/23.jpg'),
            16 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/24.jpg'),
            17 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/25.jpg'),
            18 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/26.jpg'),
            19 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/27.jpg'),
            20 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/3.jpg'),
            21 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/4.jpg'),
            22 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/5.jpg'),
            23 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/6.jpg'),
            24 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/7.jpg'),
            25 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/8.jpg'),
            26 => array('https://killingkittens-beta.s3-eu-west-1.amazonaws.com/gesture_copy/9.jpg'),
        );

        foreach($image_urls as $image_url){
            $gesture    =   new Gestures;
            $gesture->title =   'Title for gesture -';
            $gesture->instructions =   'Instruction for gesture -';
            $gesture->media_id  =   1; //REDO
            $gesture->save();

            $gesture->addMediaFromUrl($image_url[0])->toMediaCollection('gestures', 's3');

        }
    }
}
