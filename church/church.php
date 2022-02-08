 <?php
/**
* Plugin Name: c.h.c.
* Plugin URI: https://www.yourwebsiteurl.com/
* Description: This is the very first plugin I ever created.
* Version: 1.0
* Author: Your Name Here
* Author URI: http://yourwebsiteurl.com/
**/

add_action( 'init', 'render_home_page' );
 

add_action( 'init', 'process_post' );
 


function render_home_page() {
    ?>

    <?php if (! empty($_GET['home'])) : ?>


        <div class="home-item-wrapper">
            <h2>Speak these words, and be reborn:</h2>
            <h3>"I devote myself to uplifting humanity, by illuminating the path toward peace and harmony.</h3>
            <h3>Upon the foundation of compassion is my conscience built.</h3>
            <h3>I am whole and complete.</h3>
            <h3>Within myself, I seek my salvation."</h3>
        </div>


    <?php endif; ?>

    <?php
}


function process_post() {
    if (! empty($_GET['api'])) {

        $cache = get_option('chc_bible_data');

        if ($cache && empty($_GET['reset'])) {
            $output = $cache;
        } else {
            $data = build_result();
            $output = json_encode($data);
            update_option('chc_bible_data', $output);
        }

        echo $output;
        exit();
    }
}

function get_request(string $url = 'https://www.boredapi.com/api/activity') {

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Church');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, true);
}

function build_result(){

    $data = [];
    $books = get_request('https://api.github.com/repos/leaghbranner/humanconscience/contents');

    // echo json_encode($books);
    // exit();

    foreach ($books as $book){
        $type = $book['type'] ?? '';

        if ($type != 'dir') {
            continue;
        }

        $book_url = $book['url'] ?? '';
        $book_data = get_request($book_url);
        $chapters = [];

        foreach ($book_data as $chapter){
            $chapter_data = get_request($chapter['url'] ?? '');

            $chapters[] = [
                'chapter_name' => str_replace('.txt', '', $chapter['name'] ?? ''),
                'chapter_content' => base64_decode($chapter_data['content'] ?? ''),
            ];
        }

        $data[] = [
            'book_name' => $book['name'] ?? '',
            'chapters' => $chapters,
        ];
    }
    
    return $data;
}
