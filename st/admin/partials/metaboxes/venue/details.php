<?php ?>
 <p>
   <label>Seating Limit</label><br />
   <input type="text" name="libby[_seating_limit]" value="<?php echo isset( $seating_limit ) ? esc_attr( $seating_limit ) : ''; ?>" />
 </p>
 <p>
   <label>Room #</label><br />
   <input type="text" name="libby[_room_number]" value="<?php echo isset( $room_number ) ? esc_attr( $room_number ) : ''; ?>" />
 </p>
 <?php if ($all_branches->have_posts()): ?>
   <label>Branch</label><br />
   <select name="libby[_branch]" id="branch">
    <option></option>
     <?php while ($all_branches->have_posts()): $all_branches->the_post(); ?>
       <option value="<?php the_ID(); ?>" <?php isset($branch) ? selected( $branch, get_the_ID() ) : ''; ?>><?php the_title(); ?></option>
     <?php endwhile; ?>
   </select>

   <?php endif; ?>
 <p>
   <label>
     <input type="checkbox" name="libby[_staff_only]" value="1" <?php isset( $staff_only ) ? checked( $staff_only ) : ''; ?> />
     Staff Only</label>
 </p>
<?php
