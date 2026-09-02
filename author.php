<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$author = get_queried_object();
if ( ! ( $author instanceof WP_User ) ) {
    echo '<main class="pv-author-shell"><div class="pv-author-panel"><p class="pv-author-empty">Kullanıcı bulunamadı.</p></div></main>';
    get_footer();
    return;
}

$author_id      = (int) $author->ID;
$current_id     = get_current_user_id();
$is_self        = ( $current_id === $author_id );
$follower_count = function_exists( 'pv_author_follow_count' ) ? pv_author_follow_count( $author_id, 'followers' ) : 0;
$following_count= function_exists( 'pv_author_follow_count' ) ? pv_author_follow_count( $author_id, 'following' ) : 0;
$is_following   = function_exists( 'pv_author_is_following' ) ? pv_author_is_following( $current_id, $author_id ) : false;
$bio            = (string) get_user_meta( $author_id, 'biyografi', true );
$facebook       = esc_url( (string) get_user_meta( $author_id, 'facebook', true ) );
$twitter        = esc_url( (string) get_user_meta( $author_id, 'twitter', true ) );
$instagram      = esc_url( (string) get_user_meta( $author_id, 'instagram', true ) );
$post_count     = (int) count_user_posts( $author_id, 'post', true );
$follower_ids   = function_exists( 'pv_author_follow_ids' ) ? pv_author_follow_ids( $author_id, 'followers', 24 ) : array();
$following_ids  = function_exists( 'pv_author_follow_ids' ) ? pv_author_follow_ids( $author_id, 'following', 24 ) : array();

$favorites = new WP_Query( array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 12,
    'ignore_sticky_posts' => true,
    'meta_query'          => array(
        array(
            'key'     => '_user_liked',
            'value'   => (string) $author_id,
            'compare' => 'LIKE',
        ),
    ),
) );

function pv_author_render_post_card() {
    $post_id = get_the_ID();
    echo '<article class="pv-author-card">';
    echo '<a class="pv-author-card-thumb" href="' . esc_url( get_permalink() ) . '">';
    if ( has_post_thumbnail() ) {
        echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'loading' => 'lazy' ) );
    }
    echo '</a>';
    echo '<div class="pv-author-card-body">';
    echo '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
    echo '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 20 ) ) . '</p>';
    echo '</div></article>';
}

function pv_author_render_member_cards( $ids ) {
    if ( empty( $ids ) ) {
        echo '<div class="pv-author-empty">Henüz kullanıcı bulunmuyor.</div>';
        return;
    }
    echo '<div class="pv-author-members">';
    foreach ( $ids as $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) { continue; }
        echo '<a class="pv-author-member" href="' . esc_url( get_author_posts_url( $user->ID ) ) . '">';
        echo get_avatar( $user->ID, 46 );
        echo '<span>' . esc_html( $user->display_name ) . '</span>';
        echo '</a>';
    }
    echo '</div>';
}
?>

<main class="pv-author-shell">
    <section class="pv-author-hero">
        <div class="pv-author-avatar"><?php echo get_avatar( $author_id, 118 ); ?></div>

        <div class="pv-author-meta">
            <h1><?php echo esc_html( $author->display_name ); ?></h1>
            <?php if ( '' !== trim( $bio ) ) : ?>
                <p class="pv-author-bio"><?php echo esc_html( $bio ); ?></p>
            <?php endif; ?>

            <div class="pv-author-stats">
                <span class="pv-author-stat"><b data-pv-follower-count><?php echo esc_html( $follower_count ); ?></b> takipçi</span>
                <span class="pv-author-stat"><b><?php echo esc_html( $following_count ); ?></b> takip</span>
                <span class="pv-author-stat"><b><?php echo esc_html( $post_count ); ?></b> içerik</span>
            </div>
        </div>

        <div class="pv-author-actions">
            <?php if ( $is_self ) : ?>
                <a class="pv-author-btn" href="<?php echo esc_url( function_exists( 'pv_member_url' ) ? pv_member_url( 'uye-profili' ) : home_url( '/uye-profili/' ) ); ?>">Profili Düzenle</a>
            <?php elseif ( is_user_logged_in() ) : ?>
                <button class="pv-author-btn pv-author-follow" type="button" data-pv-author-follow data-user-id="<?php echo esc_attr( $author_id ); ?>" data-following="<?php echo $is_following ? '1' : '0'; ?>"><?php echo $is_following ? 'Takipten Çık' : 'Takip Et'; ?></button>
            <?php else : ?>
                <a class="pv-author-btn" href="<?php echo esc_url( function_exists( 'pv_member_login_url' ) ? pv_member_login_url() : wp_login_url() ); ?>">Takip Et</a>
            <?php endif; ?>

            <?php if ( $facebook || $twitter || $instagram ) : ?>
                <div class="pv-author-social" aria-label="Sosyal medya">
                    <?php if ( $facebook ) : ?><a href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ( $twitter ) : ?><a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
                    <?php if ( $instagram ) : ?><a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <nav class="pv-author-tabs" aria-label="Profil içeriği">
        <button class="pv-author-tab is-active" type="button" data-pv-author-tab="posts">Paylaşımları</button>
        <button class="pv-author-tab" type="button" data-pv-author-tab="favorites">Favorileri</button>
        <button class="pv-author-tab" type="button" data-pv-author-tab="followers">Takipçileri</button>
        <button class="pv-author-tab" type="button" data-pv-author-tab="following">Takip Ettikleri</button>
    </nav>

    <section class="pv-author-panel" data-pv-author-panel="posts">
        <?php if ( have_posts() ) : ?>
            <div class="pv-author-grid">
                <?php while ( have_posts() ) : the_post(); pv_author_render_post_card(); endwhile; ?>
            </div>
            <?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '‹', 'next_text' => '›' ) ); ?>
        <?php else : ?>
            <div class="pv-author-empty">Bu kullanıcının henüz yayınlanmış içeriği yok.</div>
        <?php endif; ?>
    </section>

    <section class="pv-author-panel" data-pv-author-panel="favorites" hidden>
        <?php if ( $favorites->have_posts() ) : ?>
            <div class="pv-author-grid">
                <?php while ( $favorites->have_posts() ) : $favorites->the_post(); pv_author_render_post_card(); endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <div class="pv-author-empty">Favorilere eklenmiş içerik bulunmuyor.</div>
        <?php endif; ?>
    </section>

    <section class="pv-author-panel" data-pv-author-panel="followers" hidden>
        <?php pv_author_render_member_cards( $follower_ids ); ?>
    </section>

    <section class="pv-author-panel" data-pv-author-panel="following" hidden>
        <?php pv_author_render_member_cards( $following_ids ); ?>
    </section>
</main>

<?php get_footer();
