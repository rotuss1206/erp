<i class="fa fa-shopping-cart"></i>

<div class="timeline-item" id="timeline-item-{{ feed.id }}">
    <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

    <h3 class="timeline-header timeline-header-order-note">
        <span class="timeline-feed-header-text">
            {{{feed.message}}}
        </span>
    </h3>
</div>